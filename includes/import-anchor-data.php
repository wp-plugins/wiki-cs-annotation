<?php

function import_anchor_data($xmlfile) {

    require_once('wiki-cs-database-api.php');

    // Parse file
    $anchor_entries = parse_xml($xmlfile);

    // No entries found ? - then aborted.
    if (!$anchor_entries)
        return 0;

    // Initialises a variable storing the number of anchor entry successfully imported.
    $imported = 0;

    foreach ($anchor_entries as $anchor_entry) {

        //Check if the anchor text already exists
        $anchor_text_exists = wca_get_anchor_by_anchor_text((string) $anchor_entry['anchor_text']);

        //If it doesn't exists
        if (!$anchor_text_exists) {
            //Insert to wp_wca_anchor table
            $inserted_anchor_id = wca_insert_anchor(array(
                        'anchor_text' => $anchor_entry['anchor_text'],
                        'link_a' => $anchor_entry['link_a'],
                        'freq_a' => $anchor_entry['freq_a'],
                    ));
        } else {
            $inserted_anchor_id = $anchor_text_exists[0]->anchor_id;

            //Check if link_a or freq_a doesn't have same value with one which is wanted to be inserted
            if (($anchor_text_exists[0]->link_a != $anchor_entry['link_a']) || ($anchor_text_exists[0]->freq_a != $anchor_entry['freq_a'])) {
                //Update link_a and freq_a
                $update_anchor_success = wca_update_anchor($inserted_anchor_id, array(
                            'anchor_text' => $anchor_text_exists[0]->anchor_text,
                            'link_a' => $anchor_entry['link_a'],
                            'freq_a' => $anchor_entry['freq_a'],
                        ));

                if (!$update_anchor_success)
                    die('Update link_a or freq_a unsuccessful');
            }
        }

        $anchor_sense_maps = $anchor_entry['anchor_sense_maps'];
        foreach ($anchor_sense_maps as $anchor_sense_map) {

            //Check if the anchor sense map already exists
            $anchor_sense_map_exists = wca_get_anchor_sense_map_by_anchor_id_and_sense_title($inserted_anchor_id, (string) $anchor_sense_map['sense_title']);

            //If it doesn't exists
            if (!$anchor_sense_map_exists) {
                //Insert to wp_wca_anchor_sense_map
                $inserted_map_id = wca_insert_anchor_sense_map(array(
                            'anchor_id' => $inserted_anchor_id,
                            'sense_title' => $anchor_sense_map['sense_title'],
                            'counter' => $anchor_sense_map['counter'],
                        ));
            } else {
                $inserted_map_id = $anchor_sense_map_exists[0]->map_id;

                //If counter doesn't have the same value
                if ($anchor_sense_map_exists[0]->counter != $anchor_sense_map['counter']) {
                    //Update counter
                    $update_counter_map_success = wca_update_anchor_sense_map($inserted_map_id, array(
                                'anchor_id' => $anchor_sense_map_exists[0]->anchor_id,
                                'sense_title' => $anchor_sense_map_exists[0]->sense_title,
                                'counter' => $anchor_sense_map['counter'],
                            ));

                    if (!$update_counter_map_success)
                        die('Update counter unsuccessful');
                }
            }

            $in_page_titles = $anchor_sense_map['in_page_title_set'];
            foreach ($in_page_titles as $in_page_title) {

                //Check if the wiki title already exists
                $page_wiki_title_exists = wca_get_page_wiki_id_by_title((string) $in_page_title);

                //Var to store page_wiki_id
                $page_wiki_id_insert_in_page = 0;

                // If it isn't exists, insert it
                if (!$page_wiki_title_exists) {
                    //Insert to wp_wca_page_wiki
                    $inserted_page_wiki_id = wca_insert_page_wiki(array(
                                'page_wiki_title' => $in_page_title,
                            ));

                    $page_wiki_id_insert_in_page = (int) $inserted_page_wiki_id;
                } else {
                    $page_wiki_id_insert_in_page = (int) $page_wiki_title_exists[0]->page_wiki_id;
                }

                //Check if map_id already exists in wp_wca_in_page table
                $in_page_map_id_page_wiki_exists = wca_get_in_page_by_map_id_and_page_wiki_id($inserted_map_id, $page_wiki_id_insert_in_page);

                //If doesn't exists
                if (!$in_page_map_id_page_wiki_exists) {
                    //Insert to wp_wca_in_page
                    wca_insert_in_page(array(
                        'map_id' => $inserted_map_id,
                        'page_wiki_id' => $page_wiki_id_insert_in_page,
                    ));
                }
            }
        }

        //increment $imported
        $imported++;
    }

    return $imported;
}

function parse_xml($xmlfile) {

    $doc = new DOMDocument();
    $doc->load($xmlfile);

    // Initial anchor entries array
    $entries = array();

    $anchor_entries = $doc->getElementsByTagName("anchor_entry");
    foreach ($anchor_entries as $anchor_entry) {
        $anchors = $anchor_entry->getElementsByTagName("anchor_text");
        $anchor = $anchors->item(0)->nodeValue;

        $links_a = $anchor_entry->getElementsByTagName("link_a");
        $link_a = $links_a->item(0)->nodeValue;

        $freqs_a = $anchor_entry->getElementsByTagName("freq_a");
        $freq_a = $freqs_a->item(0)->nodeValue;

        $anchor_sense_maps = $anchor_entry->getElementsByTagName("anchor_sense_maps");
        $anchor_maps = $anchor_sense_maps->item(0)->getElementsByTagName("anchor_sense_map");
        // initial anchor_sense_map array
        $maps = array();
        foreach ($anchor_maps as $anchor_map) {
            $sense_titles = $anchor_map->getElementsByTagName("sense_title");
            $sense = $sense_titles->item(0)->nodeValue;

            $counters = $anchor_map->getElementsByTagName("counter");
            $counter = $counters->item(0)->nodeValue;

            $in_page_title_set = $anchor_map->getElementsByTagName("in_page_title_set");
            $in_page_titles = $in_page_title_set->item(0)->getElementsByTagName("in_page_title");
            // initial in_page_title_set array
            $in_titles = array();
            foreach ($in_page_titles as $in_page_title) {
                $in_titles[] = $in_page_title->nodeValue;
            }

            $maps[] = array(
                'sense_title' => (string) $sense,
                'counter' => (int) $counter,
                'in_page_title_set' => $in_titles,
            );
        }

        $entries[] = array(
            'anchor_text' => (string) $anchor,
            'anchor_sense_maps' => $maps,
            'link_a' => (int) $link_a,
            'freq_a' => (int) $freq_a,
        );
    }

    return $entries;
}

function split_xml_file($xmlfile, $dir_destination_location) {

    $doc = new DOMDocument();
    $doc->load($xmlfile);

    // Initial file counter
    $file_counter = 1;

    // Initital index
    $idx = 1;

    // Initial array for storing splitting result xml file locations
    $xml_split_locations = array();

    // Initial anchor entries array
    $entries = array();

    $anchor_entries = $doc->getElementsByTagName("anchor_entry");
    // anchor_entries_count
    $anchor_entries_count = $anchor_entries->length;
    // set to first element of returned xml_split_locations
    $xml_split_locations[] = $anchor_entries_count;
    foreach ($anchor_entries as $anchor_entry) {
        
        $anchors = $anchor_entry->getElementsByTagName("anchor_text");
        if($anchors->length <= 0) throw new Exception("XML file does not have appropriate element");
        $anchor = $anchors->item(0)->nodeValue;

        $links_a = $anchor_entry->getElementsByTagName("link_a");
        if($links_a->length <= 0) throw new Exception("XML file does not have appropriate element");
        $link_a = $links_a->item(0)->nodeValue;

        $freqs_a = $anchor_entry->getElementsByTagName("freq_a");
        if($freqs_a->length <= 0) throw new Exception("XML file does not have appropriate element");
        $freq_a = $freqs_a->item(0)->nodeValue;

        $anchor_sense_maps = $anchor_entry->getElementsByTagName("anchor_sense_maps");
        if($anchor_sense_maps->length <= 0) throw new Exception("XML file does not have appropriate element");
        $anchor_maps = $anchor_sense_maps->item(0)->getElementsByTagName("anchor_sense_map");
        if($anchor_maps->length <= 0) throw new Exception("XML file does not have appropriate element");
        
        // initial anchor_sense_map array
        $maps = array();
        foreach ($anchor_maps as $anchor_map) {
            $sense_titles = $anchor_map->getElementsByTagName("sense_title");
            if($sense_titles->length <= 0) throw new Exception("XML file does not have appropriate element");
            $sense = $sense_titles->item(0)->nodeValue;

            $counters = $anchor_map->getElementsByTagName("counter");
            if($counters->length <= 0) throw new Exception("XML file does not have appropriate element");
            $counter = $counters->item(0)->nodeValue;

            $in_page_title_set = $anchor_map->getElementsByTagName("in_page_title_set");
            if($in_page_title_set->length <= 0) throw new Exception("XML file does not have appropriate element");
            $in_page_titles = $in_page_title_set->item(0)->getElementsByTagName("in_page_title");
            if($in_page_titles->length <= 0) throw new Exception("XML file does not have appropriate element");
            // initial in_page_title_set array
            $in_titles = array();
            foreach ($in_page_titles as $in_page_title) {
                $in_titles[] = $in_page_title->nodeValue;
            }

            $maps[] = array(
                'sense_title' => (string) $sense,
                'counter' => (int) $counter,
                'in_page_title_set' => $in_titles,
            );
        }

        $entries[] = array(
            'anchor_text' => (string) $anchor,
            'anchor_sense_maps' => $maps,
            'link_a' => (int) $link_a,
            'freq_a' => (int) $freq_a,
        );

        if ($idx == 50) {
            //reset idx
            $idx = 0;

            // create xml file
            $xml = new DOMDocument();
            $xml_root = generate_xml_element($xml, $entries);
            if ($xml_root)
                $xml->appendChild($xml_root);
            $xml->formatOutput = true;
            $xml_file_location = "anchor_entries_part" . $file_counter . '_' . time() . ".xml";

            $xml->save($dir_destination_location.'/'.$xml_file_location);

            // xml file locations
            $xml_split_locations[] = $xml_file_location;

            // increment file counter
            $file_counter++;

            // renew anchor entries array
            $entries = array();
        }

        $idx++;
    }

    // write the left over
    // create xml file
    $xml = new DOMDocument();
    $xml_root = generate_xml_element($xml, $entries);
    if ($xml_root)
        $xml->appendChild($xml_root);
    $xml->formatOutput = true;
    $xml_file_location = "anchor_entries_part" . $file_counter . '_' . time() . ".xml";
    $xml->save($dir_destination_location.'/'.$xml_file_location);

    // xml file locations
    $xml_split_locations[] = $xml_file_location;

    return $xml_split_locations;
}

function generate_xml_element($dom, $anchor_entries) {

    // Create anchorentries element (root)
    $root_element = $dom->createElement("anchorentries");

    foreach ($anchor_entries as $anchor_entry) {

        // create element anchor_entry
        $anchor_entry_element = $dom->createElement("anchor_entry");

        // create element anchor_text
        $anchor_element = $dom->createElement("anchor_text");
        $anchor_text = $dom->createTextNode($anchor_entry["anchor_text"]);
        $anchor_element->appendChild($anchor_text);

        $anchor_sense_maps_element = $dom->createElement("anchor_sense_maps");
        $anchor_maps = $anchor_entry["anchor_sense_maps"];
        foreach ($anchor_maps as $map) {
            // create element anchor_sense_map
            $anchor_sense_map_element = $dom->createElement("anchor_sense_map");

            // create element sense_title
            $sense_title_element = $dom->createElement("sense_title");
            $sense_title_text = $dom->createTextNode($map["sense_title"]);
            $sense_title_element->appendChild($sense_title_text);
            // create element counter
            $counter_element = $dom->createElement("counter", $map["counter"]);
            // create element in_page_title_set
            $in_page_title_set_element = $dom->createElement("in_page_title_set");
            $in_page_title_set = $map["in_page_title_set"];
            foreach ($in_page_title_set as $in_page_title) {
                // create element in_page_title
                $in_page_title_element = $dom->createElement("in_page_title", $in_page_title);
                // append to in_page_title_set element
                $in_page_title_set_element->appendChild($in_page_title_element);
            }

            // append to anchor_sense_map element
            $anchor_sense_map_element->appendChild($sense_title_element);
            $anchor_sense_map_element->appendChild($counter_element);
            $anchor_sense_map_element->appendChild($in_page_title_set_element);

            // append to anchor_sense_maps element
            $anchor_sense_maps_element->appendChild($anchor_sense_map_element);
        }

        // create element link_a
        $link_a_element = $dom->createElement("link_a", $anchor_entry["link_a"]);

        // create element freq_a
        $freq_a_element = $dom->createElement("freq_a", $anchor_entry["freq_a"]);

        // append to anchor_entry element
        $anchor_entry_element->appendChild($anchor_element);
        $anchor_entry_element->appendChild($anchor_sense_maps_element);
        $anchor_entry_element->appendChild($link_a_element);
        $anchor_entry_element->appendChild($freq_a_element);

        // append to root element
        $root_element->appendChild($anchor_entry_element);
    }

    return $root_element;
}