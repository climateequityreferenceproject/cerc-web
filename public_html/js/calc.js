var regionCountryData;
var clickedDef = false;
var popup_size = {width: null, length: null};

$(function() {
    
    init_calc_behavior();
    
    // fieldset show/hide
    $("legend").click(function() {
        if (!clickedDef) {
            $(this).siblings().toggle("fast");
            if($(this).hasClass('open')) {
                $(this).removeClass('open');
                $(this).addClass('closed');
            } else if($(this).hasClass('closed')) {
                $(this).removeClass('closed');
                $(this).addClass('open');
            }
        }
    });
	
    $("#country_list_button button").click(function() {
        $('#filterDiv').dialog('open');
    });


    $("legend").siblings().hide();
    $("legend.open").siblings().show();
	
    $('#loading').hide();
	   
    $('#save').append('<button id="spinoff_button" type="button">Copy view to new window</button>');
    
    // If JS is enabled, hide the submit button & show the region-country filter + basic/adv
    $('#submit').hide();
//    $('#region_country_filter').show(); // country report is now default, 
//    so by default, region_country_filter should hide
    
    // Region list actions
    $('#regionList').click(changeRegionList);
    $('#btnAdd').click(function () {
        moveElement('country_available','country_selected');
    });
    $('#btnRemove').click(function () {
        moveElement('country_selected','country_available');
    });
    
    // Make sure display options are shown/hidden consistent with the chosen view
    set_display();
    
    $('#loading').html('<img src="img/spinner.gif" alt="loading indicator" />');
    
    //--------------------------------------------------
    // User actions that result in a refresh of the page
    //--------------------------------------------------
    $('#basic, #adv').click(function() {
        set_display();
        submit();
    });
    
    $('#table_view').change(function () {
        set_display();
        submit();
        if ($('#table_view').val() === 'gdrs_country_report') {
            $('#region_country_filter').hide();
        } else {
            $('#region_country_filter').show();
        }
    });
    
    // Equity settings panel
    $("a[id|='cbdr']").click(cbdr_grid_select);
    
    // Toggle lux threshold
    $('#do_luxcap, #interp_btwn_thresh').click(lux_thresh_activate);
    
    $('#display_yr').change(submit);
    $('#display_ctry').change(submit);
    $('#decimal_pl').change(submit);
    $('#emergency_path').change(submit);
    
    $('#baseline').change(submit);
    $('#cum_since_yr').change(submit);
    $('#use_lulucf').change(submit);
    $('#use_nonco2').change(submit);
    $('#use_netexports').change(submit);
    
    $('#dev_thresh').change(submit);
    $('#lux_thresh').change(submit);
    $('#do_luxcap').change(submit);
    $('#luxcap_mult').change(submit);
    $('#interp_btwn_thresh').change(submit);
    
    $('#r_wt').change(submit);
    // $('#percent_gwp').change(submit);
    $('#percent_gwp_MITIGATION').change(submit);
    $('#percent_gwp_ADAPTATION').change(submit);
    $('#em_elast').change(submit);
    
    $('#use_kab').change(submit);
    $('#dont_use_kab').change(submit);
    $('#kab_only_ratified').change(submit);
    
    $('#use_mit_lag').change(submit);
    
    $('#use_sequencing').change(submit);
    // TODO: These should only be run if use sequencing is checked
    $('#percent_a1_rdxn').change(submit);
    $('#base_levels_yr').change(submit);
    $('#end_commitment_period').change(submit);
    $('#a1_smoothing').change(submit);
    $('#mit_gap_borne').change(submit);
    
    // Equity settings
    $('#dev-low, #dev-med').click(function() {
        $('#equity_progressivity').val(0);
        cbdr_select();
    });
    $('#dev-high').click(function() {
        $('#equity_progressivity').val(1);
        cbdr_select();
    });
    
    $('#r100, #r50c50, #c100').click(cbdr_select);
    
    $('#equity_reset, #equity_reset_top').click(function() {
        $('#equity_progressivity').val(0);
        $('#ambition-high').attr('checked','checked');
        $('#r50c50').attr('checked','checked');
        $('#dev-med').attr('checked','checked');
        $('#d1990').attr('checked','checked');
        cbdr_select();
        // Short-circuit form submission
        return false;
    })

    //--------------------------------------------------
    // Action on form submit
    //--------------------------------------------------
    $('#submit').click(function() {
        // show spinner
        $('#loading').show();
        // Get current year
        var curr_year = $('#cum_since_yr').val();
        
        $.post(
            "core.php",
            "getdb=yes",
            function(dbname) {
                $('#user_db').val(dbname);
                $.post(
                    "core.php",
                    $('#form1').serialize() + "&submit=submit&ajax=ajax",
                    function(data) {
                        $('#data').html(data);
                        init_calc_behavior();
                        // hide spinner
                        $('#loading').hide();

                        //filter result
                        filterResult();

                        // No longer updating year list: just issue an alert
                        $('#cum_since_yr').load('get_year_list.php option', $('#form1').serialize(), function(){
                            var min_year = $('#cum_since_yr option').attr('value');
                            var new_year = Math.max(curr_year, min_year);
                            $('#cum_since_yr').val(new_year);
                            if (new_year != curr_year) {
                                alert("Note: data for non-CO<sub>2</sub> emissions at the national level go back only to" + new_year);
                            }
                        });
                    }
                );
            }
        );

        // Short-circuit form submission
        return false;
    });
        
    $('#spinoff_button').click(function() {
        spinoff_window();
    });

    //dialog div
    $('#filterDiv').dialog({
        title: 'Select regions and countries',
        autoOpen: false,
        height: 300,
        width: 700,
        buttons: {
            'OK': function() {
                $(this).dialog("close");
            }
        },
        close: function(event, ui) {
            $('#current_list').empty();
            $('#current_list').append($('#country_selected option').clone());
            filterResult();
        },
        zIndex: 1

    });

    //get region country data
    // show spinner
    $('#loading').show();
    $.post("./tables/get_region_country.php", {
        action:'getData',
        user_db:$('#user_db').val()
    }, function(data){
        regionCountryData=data;
        for (index in regionCountryData.allRegion){
            var regionName=regionCountryData.allRegion[index].name_S;
            var regionLongName=regionCountryData.allRegion[index].name_L;
            $('#regionList').append('<option value="'+regionName+'" title="'+regionLongName+'">'+regionLongName+'</option>');
        }
        changeRegionList();
        // hide spinner
        $('#loading').hide();
    }, 'json');

});

function cbdr_grid_select() {
    var id_match = /\d+/.exec($(this).attr('id'));
    var id = parseInt(id_match[0]);
    
    var rvsc = Math.floor((id - 1)/3);
    var prog = (id - 1) % 3;
    
    switch (rvsc.toString()) {
        case '0':
            $('#r100').attr('checked',true);
            break;
        case '1':
            $('#r50c50').attr('checked',true);
            break;
        case '2':
            $('#c100').attr('checked',true);
            break;
        default:
            ;
    }

    switch (prog.toString()) {
        case '0':
            $('#dev-low').attr('checked',true);
            break;
        case '1':
            $('#dev-med').attr('checked',true);
            break;
        case '2':
            $('#dev-high').attr('checked',true);
            break;
        default:
            ;
    }
    
    for (var i = 1; i <= 9; i++) {
        var istring = '#cbdr-' + i;
        if (i === id) {
            $(istring).addClass('selected');
        } else {
            $(istring).removeClass('selected');
        }
    }
}

function cbdr_select() {
    switch ($('#equity_settings input[name=r_wt]:checked').attr("id")) {
        case 'r100':
            id = 0;
            break;
        case 'r50c50':
            id = 3;
            break;
        case 'c100':
            id = 6;
            break;
        default:
            id = -10;
    }
    
    switch ($('#equity_settings input[name=dev_thresh]:checked').attr("id")) {
        case 'dev-low':
            id += 1;
            break;
        case 'dev-med':
            id += 2;
            break;
        case 'dev-high':
            id +=3;
            break;
        default:
            id = -10;
    }
    for (var i = 1; i <= 9; i++) {
        var istring = '#cbdr-' + i;
        if (i === id) {
            $(istring).addClass('selected');
        } else {
            $(istring).removeClass('selected');
        }
    }
}

function get_def_by_id(e) {
    // Hacky but effective
    clickedDef = true;
    setTimeout(function() {clickedDef = false;},10);
    href = $(e.currentTarget).attr("href");
    def_id = href.substr(href.lastIndexOf('#') + 1);
    
    if (!popup_size.width || !popup_size.height) {
        popup_size = {
            // The -20 takes care of the border
            width: Math.min(500, screen.width - 20),
            height: Math.min(300, screen.height - 20)
        };
    }
    
    $.getJSON('glossary_array.php', {id: def_id}, function(definition){
       $('#popup').html(definition.text).dialog({
            autoOpen: false,
            title: definition.label,
            width: popup_size.width,
            height: popup_size.height,
            resizeStop: function( event, ui ) {popup_size = ui.size;}
       });
       
       $('#popup').dialog('open');
       
       $('#popup').find('a').each(function() {
            if ($(this).attr('target') == '_self') {
                $(this).addClass('def_link');
                $(this).click(get_def_by_id);
            }
        });

    });
    e.preventDefault();
}

function lux_thresh_activate() {
    if ($('#do_luxcap').is(':checked') || $('#interp_btwn_thresh').is(':checked')) {
        $('#lux_thresh').removeAttr("disabled");
        $('#luxcap_mult').removeAttr("disabled");
    } else {
        $('#lux_thresh').attr("disabled",true);
        $('#luxcap_mult').attr("disabled",true);
    }
}

function init_calc_behavior() {
    // Make table sortable
    $(".tablesorter").tablesorter();
    // Set state of dependent form elements appropriately
    lux_thresh_activate();
    // Set the parameters to show or hide
    $("#input_values caption, #toggle-key").hover(function() {
        $(this).addClass('pretty-hover');
    }, function() {
        $(this).removeClass('pretty-hover');
    });
    // Set the parameters to show or hide
    $('#input_values caption').click(function() {
        $('#input_values tbody').toggle(function() {
            $('#input_values caption a').text(
              $(this).is(':visible') ? "Hide settings" : "Show settings"
            );
        });
    });
    // Set the graph key to show or hide
    $('#toggle-key').click(function() {
        $(this).next().toggle(function() {
            $('#toggle-key').text(
              $('#toggle-key').next().is(':visible') ? "Hide graph key" : "Show graph key"
            );
        });
    });
    // Enable help links
    $('a.def_link').click(
        get_def_by_id
    );
//    $('dl#ctry_report_legend dt').click(function() {
//        $(this).nextUntil("dt").toggle();
//    });
//    $('dl#ctry_report_legend dt').hover(function() {
//        $(this).addClass('pretty-hover');
//    }, function() {
//        $(this).removeClass('pretty-hover');
//    });    
}

function testajax() {
    $.post('js/testajax.php', function(data) {
        $('#testajax').html(data);
    });
}

function submit() {
    $('#submit').click();
}

function set_display() {
    country_set = {gdrs_country_report: 1};
    timeseries_set = {gdrs_RCI: 1, gdrs_alloc: 1, gdrs_alloc_pc: 1};
    if ($('#table_view').val() in country_set) {
        $('#display_ctry').parent().show();
        $('#decimal_pl').parent().hide();
    } else {
        $('#display_ctry').parent().hide();
        $('#decimal_pl').parent().show();
    }
    if ($('#table_view').val() in timeseries_set) {
        $('#display_yr').parent().hide();
    } else {
        $('#display_yr').parent().show();
    }

}

function uniqid()
{
    var newDate = new Date;
    return newDate.getTime();
}

function spinoff_window() {
    //$("#spinoff #input_values caption").show();
    html = '<!DOCTYPE html>\n' +
    '    <head>\n' +
    '       <meta charset="utf-8">\n' +
    '       <meta http-equiv="X-UA-Compatible" content="IE=edge">\n' +
    '       <title>View -- generated from the Climate Effort-Sharing Calculator</title>\n' +
    '       <link rel="stylesheet" href="http://www.gdrights.org/calculator_dev/css/cescalc.css" media="screen, projection" />\n' +
    '       <style type="text/css">\n' +
    '         #input_values caption {' +
    '           display:none;' +
    '         }' +
    '         #toggle-key {' +
    '           visibility:hidden;' +
    '         }' +
    '         #spinoff #input_values tbody {' +
    '           display:inline;' +
    '         }' +
    '       </style>\n' +
    '   </head>\n' +
    '   <body id="spinoff">\n' +
    '      <div id="calc_container" class="group">\n' +
    '         <div id="data" class="group">\n';
    html += $('#data').html();
    var d = new Date();
    html += '         </div>\n' +
    '      </div>\n' +
    '      <div id="footer">\n' +
    '           <p><strong>Greenhouse Development Rights</strong> is a project of <a href="http://www.ecoequity.org/">EcoEquity</a> and the <a href="http://www.sei-international.org">Stockholm Environment Institute</a> &#169; 2008-' + d.getFullYear() + ' </p>\n' +
    '      </div>\n' +
    '   </body>\n' +
    '</html>\n';
    spinoffWindow = window.open('','GDRsCalcSpinoff' + uniqid(),'width=800,height=400,left=200,top=100,scrollbars=1');
    spinoffWindow.document.write(html);
    spinoffWindow.document.close();
    spinoffWindow.focus();
}

function changeRegionList(){
    var currentRegion=$('#regionList').val();
    if(currentRegion=='world'){
        $('#country_available').empty();
        $.each(regionCountryData.allRegion, function(index){
            var match=false;
            var name_S=regionCountryData.allRegion[index].name_S;
            var name_L=regionCountryData.allRegion[index].name_L;
            $('#country_selected option').each(function(){
                if($(this).attr('value')==name_S){
                    match=true;
                    return false;
                }
            });
            if(match==false){
                $('#country_available').append('<option value="'+name_S+'">'+name_L+'</option>');
            }
        });
        $.each(regionCountryData.allCountry, function(index){
            var match=false;
            var iso3=regionCountryData.allCountry[index].iso3;
            var countryName=regionCountryData.allCountry[index].name;
            $('#country_selected option').each(function(){
                if($(this).attr('value')==iso3){
                    match=true;
                    return false;
                }
            });
            if(match==false){
                $('#country_available').append('<option value="'+iso3+'">'+countryName+'</option>');
            }
        });
    }else{
        $('#country_available').empty();
        $.each(regionCountryData.regionCountry[currentRegion]['country'], function(index){
            var match=false;
            var iso3=regionCountryData.regionCountry[currentRegion]['country'][index];
            $('#country_selected option').each(function(){
                if($(this).attr('value')==iso3){
                    match=true;
                    return false;
                }
            });
            if(match==false){
                var countryName;
                $.each(regionCountryData.allCountry, function(index){
                    if (iso3==regionCountryData.allCountry[index].iso3){
                        countryName=regionCountryData.allCountry[index].name;
                        return false;
                    }
                });
                $('#country_available').append('<option value="'+iso3+'">'+countryName+'</option>');
            }
            
        });
    }
}

function filterResult(){
    if($('#current_list option').size()==0){
        $('table tr td.cr_item').each(function(){
            $(this).parent().show();
        });
    }else{
        $('table tr td.cr_item').each(function(){
            var display=false;
            var name=$(this).text();
            //if string start with (n), remove it
            if(name.indexOf('(')==0){
                name=name.substring(name.indexOf(') ')+2);
            }
            $('#current_list option').each(function(){
                var listItem=$(this).text();
                //                if(name.match(listItem+'$')==listItem){
                //                    display=true;
                //                }
                if(name==listItem){
                    display=true;
                }
            });
            if(display){
                $(this).parent().show();
            }else{
                $(this).parent().hide();
            }
        });
    }
    
}

function moveElement(selectFrom,selectTo)
{
    $('#'+selectFrom+'>option:selected').appendTo($('#'+selectTo));
}