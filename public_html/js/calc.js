var regionCountryData;

$(function() {
    // Make table sortable
    $("#data table:eq(1)").addClass('tablesorter').tablesorter();

    // fieldset show/hide
    $("legend").click(function() {
        $(this).siblings().toggle("fast");
		
        if($(this).hasClass('open')) {
            $(this).removeClass('open');
            $(this).addClass('closed');
        } else if($(this).hasClass('closed')) {
            $(this).removeClass('closed');
            $(this).addClass('open');
        }
    });
	
    // hand cursor for hover on legend of fieldset
    $("legend").hover(function() {
        $(this).addClass('pretty-hover');
    }, function() {
        $(this).removeClass('pretty-hover');
    });


    $("legend").siblings().hide();
    $("legend.open").siblings().show();
	
    $('#loading').hide();
	   
    $('#save').append('<button id="spinoff" type="button">Copy table to new window</button>');
    
    // Allow for a hidden submit button that submits the whole form without a refresh--need to framework change
    $('#form1').append('<input type="submit" name="forcesubmit" id="forcesubmit" value="forcesubmit" />');
    $('#forcesubmit').hide();
    
    $('#framework').change(function() {
        $('#forcesubmit').click();
    });

    $('#basic').click(function() {
        $('#reset').click();
    });

    $('#adv').click(function() {
        $('#forcesubmit').click();
    });

    $('#submit').click(function() {

        // show spinner
        $('#loading').show();
		
        $.post(
            "core.php",
            $('#form1').serialize() + "&submit=submit&ajax=ajax",
            function(data) {
                $('#data').html(data);
				
                // hide spinner
                $('#loading').hide();

                //filter result
                filterResult();
            }
            );
        // Short-circuit form submission
        return false;
    });
    $('#spinoff').click(function() {
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
        $('#regionList').append('<option value="world">World</option>');
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

function uniqid()
{
    var newDate = new Date;
    return newDate.getTime();
}

function spinoff_window() {
    html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n' +
    '<html xmlns="http://www.w3.org/1999/xhtml">\n' +
    '   <head>\n' +
    '       <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />\n' +
    '       <title>Table -- generated from the Greenhouse Development Rights online calculator</title>\n' +
    '       <link rel="stylesheet" href="http://www.gdrights.org/calculator_dsn/css/gdrscalc.css" type="text/css" media="screen, projection" />\n' +
    '   </head>\n' +
    '   <body id="spinoff">\n' +
    '      <div id="calc_container">\n' +
    '         <div id="data">\n';
    html += $('#data').html();
    html +=  '              <br class="clear"/>\n' +
    '         </div>\n' +
    '         <br class="clear"/>\n' +
    '      </div>\n' +
    '      <div id="footer">\n' +
    '           <p>| <strong>Greenhouse Development Rights</strong> is a project of <a href="http://www.earthisland.org/">EcoEquity</a> and the <a href="http://www.sei-us.org">Stockholm Environment Institute</a> &#169; 2008 |</p>\n' +
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
        $('table tr td.lj').each(function(){
            $(this).parent().show();
        });
    }else{
        $('table tr td.lj').each(function(){
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