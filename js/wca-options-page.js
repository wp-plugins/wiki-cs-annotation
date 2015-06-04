var chunk_xml_count_not_imported = 0;
var anchor_entries_imported_count = 0;
var anchor_entries_total_count = 0;

function wca_chunk_form_submit(chunk_count){

    chunk_xml_count_not_imported = chunk_count;

    jQuery("#wca_progress_bar").progressbar({
        value: false,
        max: anchor_entries_total_count,
        change: function() {
            jQuery(".progress-label").text( jQuery("#wca_progress_bar").progressbar( "value" ) + " of " + anchor_entries_total_count + " anchor entries have been processed. Please wait..." );
        },
        complete: function() {
            jQuery(".progress-label").text( "Import Complete!" );
        }
    });

    jQuery(".progress-label").text( "Importing..." );

    var maxLoops = chunk_count;
    var counter = 1;

    var xml_chunk_location = document.getElementById('wca_import_chunk_location_' + counter).value;

    var data = {
        chunk_location: xml_chunk_location,
        security: ajax_object.ajax_import_nonce,
        action: 'wca_import_anchor_data'
    };

    jQuery.post(ajax_object.ajax_url, data, wca_import_chunk_success);

    (function next() {
        if (counter++ >= maxLoops) return;

        setTimeout(function() {
            var xml_chunk_location = document.getElementById('wca_import_chunk_location_' + counter).value;

            var data = {
                chunk_location: xml_chunk_location,
                security: ajax_object.ajax_import_nonce,
                action: 'wca_import_anchor_data'
            };

            jQuery.post(ajax_object.ajax_url, data, wca_import_chunk_success);

            next();
        }, 1000);
    })();

}

function wca_import_chunk_success(anchor_imported){
    chunk_xml_count_not_imported--;
    anchor_entries_imported_count += parseInt(anchor_imported);

    jQuery("#wca_progress_bar").progressbar( "value", anchor_entries_imported_count );

    // if complete
    if(chunk_xml_count_not_imported == 0){
        document.getElementById("wca_complete_message").innerHTML = '<div id="message" class="updated">\n\
            <p>Import complete. ' + anchor_entries_imported_count + ' records have been processed.</p></div>';
    }
}

function wca_set_anchor_entries_total_count(anchor_total_count){
    anchor_entries_total_count = parseInt(anchor_total_count);
}