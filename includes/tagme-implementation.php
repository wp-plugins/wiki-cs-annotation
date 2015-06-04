<?php

require_once ('wiki-cs-database-api.php');

function wca_get_tag_suggestion($content, $rhoNA){

    $content = strtolower($content);
    // anchor parsing
    $anchor_parsed = wca_anchor_parsing($content);

    // anchor disambiguation
    $page_wiki_total_count = wca_get_page_wiki_count();
    $anchor_disambiguated = wca_anchor_disambiguation($anchor_parsed, $page_wiki_total_count);

    // anchor pruning
    $anchor_pruned = wca_anchor_pruning($anchor_disambiguated, $page_wiki_total_count, $rhoNA);

    return $anchor_pruned;
}

function wca_anchor_parsing($content){

    $anchor_detected = array();

    $anchor_entries = wca_get_anchors();

    $anchor_entries_size = sizeof($anchor_entries);

    for($i = 0; $i < $anchor_entries_size; $i++){
        
        $anchor_found_position = wca_strpos_word_base($content, $anchor_entries[$i]->anchor_text);

        if($anchor_found_position !== false){
            
            $anchor_detected[] = $anchor_entries[$i];
        }
        
    }
    
    if(sizeof($anchor_detected) == 0) return;
    if(sizeof($anchor_detected) == 1) return $anchor_detected;
    return wca_anchor_parsing_check_substring($anchor_detected);
}

function wca_strpos_word_base($haystack, $needle){
    $needle_found_position = 0;
    while(($needle_found_position = strpos($haystack, $needle, $needle_found_position)) !== false){
        $char_check_after = substr($haystack, $needle_found_position + strlen($needle), 1);
        // if a character after the needle found is a (space), !, ?, ., (comma), :, ;, &lt;
        if($char_check_after == ' ' || $char_check_after == ':' ||
           $char_check_after == '!' || $char_check_after == ';' ||
           $char_check_after == '?' || $char_check_after == '.' ||
           $char_check_after == ',' ){
            if($needle_found_position == 0){
                return $needle_found_position;
            }
            else{
                $char_check_before = substr($haystack, $needle_found_position - 1, 1);
                // if a character before the needle found is a (space)
                if($char_check_before == ' '){
                    return $needle_found_position;
                }
            }
        }
        $needle_found_position = $needle_found_position + strlen($needle);
    }
    return false;
}

function wca_strpos_word_base_space_only($haystack, $needle){
    $needle_found_position = strpos($haystack, $needle);
    $needle_found_position_plus_needle_length = $needle_found_position + strlen($needle);
    $char_check_after = substr($haystack, $needle_found_position_plus_needle_length, 1);
    $char_check_before = substr($haystack, $needle_found_position - 1, 1);
    if($needle_found_position !== false){
        if($needle_found_position == 0){   
            if($char_check_after == ' '){
                return $needle_found_position;
            }
        }
        else if($needle_found_position_plus_needle_length == strlen($haystack)){
            if($char_check_before == ' '){
                return $needle_found_position;
            }
        }
        else{
            if($char_check_after == ' ' && $char_check_before == ' '){
                return $needle_found_position;
            }
        }
    }
    return false;
}

function wca_anchor_parsing_check_substring($anchor_detected){

    $anchor_not_to_be_removed = array();

    $anchor_detected_size = sizeof($anchor_detected);

    for($i = 0; $i < $anchor_detected_size; $i++){
        $is_included = true;
        for($j = 0; $j < $anchor_detected_size; $j++){
            if($i != $j){
                $anchor_now = $anchor_detected[$i];
                $anchor_to_be_compare = $anchor_detected[$j];
                // if $anchor_now is substring of $anchor_to_be_compare
                if(wca_strpos_word_base_space_only($anchor_to_be_compare->anchor_text, $anchor_now->anchor_text) !== false){
                    $lp_anchor_now = $anchor_now->link_a / $anchor_now->freq_a;
                    $lp_anchor_to_be_compare = $anchor_to_be_compare->link_a / $anchor_to_be_compare->freq_a;
                    // if lp($anchor_now) < lp($anchor_to_be_compare)
                    if($lp_anchor_now < $lp_anchor_to_be_compare){
                        $is_included = false;
                        break;
                    }
                }
            }
        }
        if($is_included) $anchor_not_to_be_removed[] = $anchor_detected[$i];
    }
    return $anchor_not_to_be_removed;
}

function wca_anchor_disambiguation($anchor_parsed, $page_wiki_total_count){

    $tau = 0.02;
    $epsilon = 0.3;

    $anchor_disambiguated_result = array();

    $anchor_to_be_disambiguate_array = wca_disambiguation_preparation($anchor_parsed);
    
    $anchor_to_be_disambiguate_array_size = sizeof($anchor_to_be_disambiguate_array);

    for($i = 0; $i < $anchor_to_be_disambiguate_array_size; $i++){
        $anchor_to_be_disambiguate_id = $anchor_to_be_disambiguate_array[$i]['anchor_id'];

        $map_data_to_be_disambiguate = $anchor_to_be_disambiguate_array[$i]['map_data'];
        $map_data_to_be_disambiguate_size = sizeof($map_data_to_be_disambiguate);

        $map_top_epsilon_size = ceil($epsilon*$map_data_to_be_disambiguate_size);
        $map_top_epsilon_init_value = array("relatedness" => 0, "idx" => -1);
        $map_top_epsilon = array_fill(0, $map_top_epsilon_size, $map_top_epsilon_init_value);

        $anchor_data['anchor_text'] = $anchor_to_be_disambiguate_array[$i]['anchor_text'];
        $anchor_data['link_prob'] = $anchor_to_be_disambiguate_array[$i]['link_prob'];

        $map_data_array = array();
        for($j = 0; $j < $map_data_to_be_disambiguate_size; $j++){

            $commonness = $map_data_to_be_disambiguate[$j]['commonness'];
            if($commonness < $tau) continue;

            $in_page_pa = $map_data_to_be_disambiguate[$j]['in_page'];
            // between 0 (not related) - 1 (most related)
            $relatedness = wca_total_annotation_score_count($anchor_to_be_disambiguate_array,
                    $anchor_to_be_disambiguate_id, $in_page_pa, $page_wiki_total_count);
            
            $map_data['sense_title'] = $map_data_to_be_disambiguate[$j]['sense_title'];
            $map_data['commonness'] = $commonness;
            $map_data['relatedness'] = $relatedness;
            $map_data['in_page'] = $in_page_pa;
            $map_data_array[] = $map_data;

            $current_idx = $j;
            for($rank = 0; $rank < $map_top_epsilon_size; $rank++){
                if($relatedness > $map_top_epsilon[$rank]["relatedness"]){
                    //swap relatedness
                    $temp_relatedness = $map_top_epsilon[$rank]["relatedness"];
                    $map_top_epsilon[$rank]["relatedness"] = $relatedness;
                    $relatedness = $temp_relatedness;
                    //swap idx
                    $temp_idx = $map_top_epsilon[$rank]["idx"];
                    $map_top_epsilon[$rank]["idx"] = $current_idx;
                    $current_idx = $temp_idx;
                }               
            }
        }

        // disambiguation by threshold
        $map_data_disambiguated = wca_disambiguation_by_threshold($map_data_array, $map_top_epsilon);

        if ($map_data_disambiguated == false) continue;
        
        $anchor_data['map_data'] = $map_data_disambiguated;

        $anchor_disambiguated_result[] = $anchor_data;

    }

    return $anchor_disambiguated_result;
}

function wca_disambiguation_by_threshold($map_data_array, $map_top_epsilon){
    
    $max_commonness = 0;
    $max_commonness_idx = -1;
    
    $map_top_epsilon_size = sizeof($map_top_epsilon);
    
    $is_all_relatedness_value_zero = true;
    for($rank = 0; $rank < $map_top_epsilon_size; $rank++){
        if($map_top_epsilon[$rank]["relatedness"] > 0) {
            $is_all_relatedness_value_zero = false;
        }
    }
    if($is_all_relatedness_value_zero) return false;

    for($rank = 0; $rank < $map_top_epsilon_size; $rank++){
        $map_idx = $map_top_epsilon[$rank]["idx"];
        if($map_idx < 0) continue;
        $current_commonness = $map_data_array[$map_idx]["commonness"];
        if($current_commonness > $max_commonness){
            $max_commonness = $current_commonness;
            $max_commonness_idx = $map_idx;
        }   
    }

    return $map_data_array[$max_commonness_idx];
}

function wca_count_relatedness($in_page_pa, $in_page_pb, $page_wiki_total_count){

    // 0 -> not related
    // 1 -> most related

    $in_page_pa_size = sizeof($in_page_pa);
    $in_page_pb_size = sizeof($in_page_pb);
    
    $in_page_pa_ids = array();
    $in_page_pb_ids = array();
    for($i = 0; $i < $in_page_pa_size; $i++){
        $in_page_pa_ids[] = $in_page_pa[$i]->page_wiki_id;
    }
    for($i = 0; $i < $in_page_pb_size; $i++){
        $in_page_pb_ids[] = $in_page_pb[$i]->page_wiki_id;
    }
    $in_page_pa_pb_size = sizeof(array_intersect($in_page_pa_ids, $in_page_pb_ids));
    if($in_page_pa_pb_size == 0) return 0;

    $numerator = log10(max($in_page_pa_size, $in_page_pb_size)) - log10($in_page_pa_pb_size);
    $denominator = log10($page_wiki_total_count) - log10(min($in_page_pa_size, $in_page_pb_size));

    $relatedness = $numerator/$denominator;
    if($relatedness > 1) $relatedness = 1;

    $relatedness = 1 - $relatedness;

    return $relatedness;
}

function wca_vote_anchor_b_to_annotation_a($anchor_b_map_data, $in_page_pa, $page_wiki_total_count){
    $anchor_b_map_data_size = sizeof($anchor_b_map_data);
    $total_vote = 0;
    for($i = 0; $i < $anchor_b_map_data_size; $i++){
        $in_page_pb = $anchor_b_map_data[$i]['in_page'];
        $relatedness_pa_pb = wca_count_relatedness($in_page_pb, $in_page_pa, $page_wiki_total_count);
        $commonness_of_pb = $anchor_b_map_data[$i]['commonness'];
        $total_vote = $total_vote + ($relatedness_pa_pb * $commonness_of_pb);
    }

    return $total_vote/$anchor_b_map_data_size;
}

function wca_total_annotation_score_count($anchor_to_be_disambiguate_array,
        $anchor_to_be_disambiguate_id, $in_page_pa, $page_wiki_total_count){
    
    $total_score = 0;

    $anchor_to_be_disambiguate_array_size = sizeof($anchor_to_be_disambiguate_array);

    for($i = 0; $i < $anchor_to_be_disambiguate_array_size; $i++){
        if($anchor_to_be_disambiguate_array[$i]['anchor_id'] == $anchor_to_be_disambiguate_id) continue;

        $anchor_b_map_data = $anchor_to_be_disambiguate_array[$i]['map_data'];

        $total_score = $total_score + wca_vote_anchor_b_to_annotation_a($anchor_b_map_data, $in_page_pa, $page_wiki_total_count);
    }

    return $total_score;
}

function wca_disambiguation_preparation($anchor_parsed){
    
    $anchor_to_be_disambiguate_array = array();
    
    $anchor_parsed_size = sizeof($anchor_parsed);
    for($i = 0; $i < $anchor_parsed_size; $i++){

        $anchor_to_be_disambiguate_data['anchor_id'] = $anchor_parsed[$i]->anchor_id;
        $anchor_to_be_disambiguate_data['anchor_text'] = $anchor_parsed[$i]->anchor_text;
        $anchor_to_be_disambiguate_data['link_prob'] = $anchor_parsed[$i]->link_a / $anchor_parsed[$i]->freq_a;

        $anchor_sense_map = wca_get_anchor_sense_map_by_anchor_id($anchor_parsed[$i]->anchor_id);
        $anchor_sense_map_size = sizeof($anchor_sense_map);
        $anchor_sense_map_data = array();
        for($j = 0; $j < $anchor_sense_map_size; $j++){
            $in_page = wca_get_in_page_by_map_id($anchor_sense_map[$j]->map_id);

            $map_data['sense_title'] = $anchor_sense_map[$j]->sense_title;
            $map_data['commonness'] = $anchor_sense_map[$j]->counter / $anchor_parsed[$i]->link_a;
            $map_data['in_page'] = $in_page;

            $anchor_sense_map_data[] = $map_data;
        }

        $anchor_to_be_disambiguate_data['map_data'] = $anchor_sense_map_data;

        $anchor_to_be_disambiguate_array[] = $anchor_to_be_disambiguate_data;
    }

    return $anchor_to_be_disambiguate_array;
}

function wca_anchor_pruning($anchor_disambiguated, $page_wiki_total_count, $rhoNA){

    $anchor_pruned = array();

    $anchor_disambiguated_size = sizeof($anchor_disambiguated);
    for($i = 0; $i < $anchor_disambiguated_size; $i++){
        
        $link_prob = $anchor_disambiguated[$i]['link_prob'];
        $in_page_pa = $anchor_disambiguated[$i]['map_data']['in_page'];
        $coherence = wca_coherence_count($i, $in_page_pa, $anchor_disambiguated, $anchor_disambiguated_size, $page_wiki_total_count);

        $pruning_score = ($link_prob + $coherence)/2;

        if($pruning_score >= $rhoNA){

            $anchor_data['anchor_text'] = $anchor_disambiguated[$i]['anchor_text'];
            $anchor_data['sense_title'] = $anchor_disambiguated[$i]['map_data']['sense_title'];
            $anchor_data['pruning_score'] = $pruning_score;

            $anchor_pruned[] = $anchor_data;
        }
    }

    return $anchor_pruned;

}

function wca_coherence_count($current_idx, $in_page_pa, $anchor_disambiguated, $anchor_disambiguated_size, $page_wiki_total_count){

    $coherence_score = 0;

    for($i = 0; $i < $anchor_disambiguated_size; $i++){
        if($i == $current_idx) continue;
        
        $in_page_pb = $anchor_disambiguated[$i]['map_data']['in_page'];

        $relatedness = wca_count_relatedness($in_page_pa, $in_page_pb, $page_wiki_total_count);

        $coherence_score = $coherence_score + $relatedness;
    }

    if($anchor_disambiguated_size == 1) return $coherence_score;
    $coherence_score = $coherence_score/($anchor_disambiguated_size-1);

    return $coherence_score;
}

?>