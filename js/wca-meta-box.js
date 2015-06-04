var suggestedTags = new Array();
var currentTags = new Array();
var suggestedAnchorSenses = new Array();
var currentAnchorSenses = new Array();
var rhoNA = 0;

function wca_pruning_slider(){
    jQuery("#wca_slider_vertical").slider({
      orientation: "vertical",
      range: "min",
      min: 0,
      max: 1.01,
      step: 0.01,
      value: 0.8,
      slide: function( event, ui ) {
        rhoNA = Math.round((1-ui.value)*100)/100;
        jQuery("#amount").val( rhoNA );
      }
    });
    
    rhoNA = Math.round((1-jQuery("#wca_slider_vertical").slider("value"))*100)/100;
    jQuery("#amount").val( rhoNA );
}

function wca_reset_rhoNA(){
    jQuery("#wca_slider_vertical").slider({
      value: 0.8
    });
    
    rhoNA = Math.round((1-jQuery("#wca_slider_vertical").slider("value"))*100)/100;
    jQuery("#amount").val( rhoNA );
}

function wca_get_suggested_tags(){
    document.getElementById('wca_tag_suggestions').innerHTML = 'Getting suggestions...';

    var data = {
        text_content: wca_get_text_content(),
        security: ajax_object.ajax_meta_box_nonce,
        rhoNA: rhoNA,
        action: 'wca_get_suggested_tags'
    };

    jQuery.post(ajax_object.ajax_url, data, wca_show_tags);
}

function wca_show_tags(response_tags){
    suggestions = document.getElementById('wca_tag_suggestions');

    if(response_tags == 'No Tags'){
        suggestions.innerHTML = 'No Tags. Set smaller value of <span style="font-style: italic;">&rho;NA</span> to get tag suggestions by using slider.';
        document.getElementById("btn_select_all_suggested").style.display = 'none';
        return;
    }

    var response_data_array = jQuery.parseJSON(response_tags);
    var response_data = null;
    suggestedTags = new Array();
    suggestedAnchorSenses = new Array();
    for(var z = 0; z < response_data_array.length; z++){
        response_data = response_data_array[z];
        suggestedTags.push(response_data.sense_title.replace(/_/g, ' '));
        suggestedAnchorSenses.push(response_data.anchor_text + "|" + response_data.sense_title);
    }
    
    suggestions.innerHTML = '';

    for (var i = 0; i < suggestedTags.length; i++) {
        var el = document.createElement('div');
        el.className = 'wca_tag';
        el.id = 'suggestion_' + i;
        var html = '<img src="' + ajax_object.wca_plugin_url + '/images/add.png" /> ' + suggestedTags[i];
        el.onclick = function(){
            wca_add_suggestion(this.id);
        }
        el.innerHTML = html;
        suggestions.appendChild(el);
    }

    document.getElementById("btn_select_all_suggested").style.display = 'block';

}

function wca_get_text_content() {
    if (typeof tinyMCE != 'undefined' && tinyMCE.activeEditor != null && tinyMCE.activeEditor.isHidden() == false) {
        return tinyMCE.activeEditor.getBody().innerHTML;
    }
    return document.getElementById('content').value;
}

function wca_add_tag(tag, anchor_sense) {
    found = false;
    for (var i = 0; i < currentTags.length; i++) {
        if (currentTags[i] == tag) found = true;
    }
    if (found == false && tag != '') {
        if (document.getElementById('wca_tag_list').value != '') {
            document.getElementById('wca_tag_list').value = document.getElementById('wca_tag_list').value + ', ' + tag;
        }
        else {
            document.getElementById('wca_tag_list').value = tag;
        }
        
        if(document.getElementById('wca_anchor_sense_list').value != ''){
            document.getElementById('wca_anchor_sense_list').value = document.getElementById('wca_anchor_sense_list').value + ', ' + anchor_sense;
        }
        else{
            document.getElementById('wca_anchor_sense_list').value = anchor_sense;
        }
    }
    wca_redisplay_tags();
}

function wca_add_suggestion(tag_id){
    id = tag_id.replace('suggestion_', '');
    tag = suggestedTags[id];
    anchor_sense = suggestedAnchorSenses[id];
    wca_add_tag(tag, anchor_sense);
    document.getElementById(tag_id).style.display = 'none';
}

function wca_redisplay_tags() {
    tags_anchor_sense_diff = wca_update_current_tags();
    existing = document.getElementById('wca_tag_assign');
    existing.innerHTML = '';
    for (var i = 0; i < currentTags.length; i++) {
        var el = document.createElement('div');
        el.className = 'wca_tag';
        el.id = 'ctag_' + i;
        var html = '<img src="' + ajax_object.wca_plugin_url + '/images/delete.png" /> ' + currentTags[i];
        el.onclick = function(){
            wca_delete_tag(this.id);
        }
        el.innerHTML = html;
        existing.appendChild(el);
    }
}

function wca_update_current_tags() {
    tempTags = document.getElementById('wca_tag_list').value.split(', ');
    tempAnchorSense = document.getElementById('wca_anchor_sense_list').value.split(', ');

    if (tempTags.length > 1) {
        currentTags = tempTags;
        currentAnchorSenses = tempAnchorSense;
    } else {
        if (tempTags[0] != ''){
            currentTags = tempTags;
            currentAnchorSenses = tempAnchorSense;
        }
    }

    return tempTags.length - tempAnchorSense.length;
}

function wca_delete_tag(tag_id){
    id = tag_id.replace('ctag_', '');
    tags_anchor_sense_diff = wca_update_current_tags();

    deleted_tag = currentTags[id];
    currentTags.splice(id, 1);
    
    idAnchorSense = id - tags_anchor_sense_diff;
    if(idAnchorSense >= 0)
        currentAnchorSenses.splice(idAnchorSense, 1);
    
    var listTag = '';
    for (var i = 0; i < currentTags.length; i++) {
        listTag += currentTags[i];
        // before last
        if (i != (currentTags.length - 1)) {
            listTag += ', ';
        }
    }
    document.getElementById('wca_tag_list').value = listTag;

    var listAnchorSense = '';
    for (var i = 0; i < currentAnchorSenses.length; i++) {
        listAnchorSense += currentAnchorSenses[i];
        // before last
        if (i != (currentAnchorSenses.length - 1)) {
            listAnchorSense += ', ';
        }
    }
    document.getElementById('wca_anchor_sense_list').value = listAnchorSense;
    
    wca_redisplay_tags();

    for (var j = 0; j < suggestedTags.length; j++) {
        if (suggestedTags[j] == deleted_tag){
            document.getElementById("suggestion_" + j).style.display = 'block';
        }
    }
}

function wca_select_all_suggested_tags(){
    for(var i = 0; i < suggestedTags.length; i++){
        tag = suggestedTags[i];
        anchor_sense = suggestedAnchorSenses[i];
        wca_add_tag(tag, anchor_sense);
        document.getElementById("suggestion_" + i).style.display = 'none';
    }
}