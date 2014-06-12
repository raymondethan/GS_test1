var generate_list = function(url,element_id,element_data)
{
    $("#"+element_id).html('<div class="loading_small"></div>');
    
    $.ajax({
        url: url,
        data: element_data,
        success: function(data){
            $("#"+element_id).html(data);
        }
    });
}

var continent_change = function(){
    $("select[name=continent]").bind('change',function(){           
        if ($(this).val() == '') {
            $("#country").parent().hide();
            $("#country").html('');
            $("#city").parent().hide();
            $("#city").html('');
        }
        else {
            $("#country").parent().show();

            location_list_params['country']['params'] = {continent: $(this).val()}
            generate_list(generate_list_url,'country',location_list_params['country']);
        }
    });
};

var country_change = function(){
    $("select[name=country]").bind('change',function(){           
        if ($(this).val() == '') {
            $("#city").parent().hide();
            $("#city").html('');
        }
        else {
            $("#city").parent().show();

            location_list_params['city']['params'] = {country: $(this).val()}
            generate_list(generate_list_url,'city',location_list_params['city']);
        }
    });
};

var load_list_table = function(){
	//if ($("#list_table").html().trim() != '') {
		//$("#list_table").fadeOut();
	//}
	$("#list_table").html('<div id="loading_line"></div>');
    $.ajax({
        url: basic_url+'/'+decodeURIComponent($.param(params)),
        success: function(data){
			$("#list_table").html(data);
			//$("#list_table").fadeIn();
			
			$("#check_all").bind('click',function(){
				$(".check_list").attr('checked',$(this).is(':checked'));
			});
			
	        $("#table_headers .order_cell").each(function(){
	            if (!(this.id.indexOf('order_') < 0)) {
					var sort_by = this.id.replace('order_','');
					$(this).addClass((params['sort_by'] == sort_by && params['order'] == 'DESC') ? 'headerSortUp' : 'headerSortDown');
					$(this).bind('click',function(){
			            	params['sort_by'] = sort_by;
			            if ($(this).hasClass('headerSortUp')) {
			            		params['order'] = 'ASC';
			                	$(this).removeClass('headerSortUp').addClass('headerSortDown');
			            }
			            else {
			            		params['order'] = 'DESC';
			            		$(this).removeClass('headerSortDown').addClass('headerSortUp');
			            	}
			            load_list_table();
					});
	            }
	        });
        }
    });
};

$(document).ready(function(){
	

});