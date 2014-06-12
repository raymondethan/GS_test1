			var o = this;
			
			o.getqs = function(param) 
			{
				var qs = new Querystring();
				return qs.get(param) == undefined ? "" : qs.get(param);
			}				

			o.selectStory = function(number) 
			{
				o.getFlashMovie("global-map").selectStory(number);

			}

			o.selectCountry = function(iso) 
			{
				o.getFlashMovie("global-map").selectCountry(iso);
				load_stats();
			}
			
			o.selectWorldMap = function()
			{
				o.getFlashMovie("global-map").selectWorldMap(null);
			}
			
			
			o.clickedStory = function(id){
				
				data="story="+id;
			 	$.ajax({
						url: '/has-been-promoted-by-user/ajax',
						type: 'GET',
						data: data, 
						success: function(data){
							var story_finder = "#story_" + id;
							if (data==1){
								$(story_finder).find(".add-to-map").hide();
								$(story_finder).find(".remove-from-map").show();
							}
							else{
								$(story_finder).find(".add-to-map").show();
								$(story_finder).find(".remove-from-map").hide();
							}
							$(".back-to-map").attr('href',window.location.pathname);
							History.pushState(null, null, stories[story_id_to_key[id]]['url']);	
					
						}
				}); 
				
			};
			
			o.userClickedCountry = function(iso) {
				update_url_param('country',iso,'string','add');
				
				ajax_updates = ['top_recent_stories'];
				update_page();
				update_history(null);
				
				if ($("#toolbar").hasClass('toolbar-expanded')) {
					GlobalStories.Map.Filter.show('toolbar-stats');
				}
				
				$("#toolbar .tabs .stats").parent().show();
				$("#toolbar-stats").show();
				load_stats();
			}
			
			o.userResetMap = function() {
				delete url_params['country'];
				
				ajax_updates = ['top_recent_stories'];
				update_page();
				update_history(null);
				
				var active_tab = $("#toolbar .ui-tabs-selected a").attr('href').replace('#','');
				
				if ($("#toolbar").hasClass('toolbar-expanded')) {
					GlobalStories.Map.Filter.show(((active_tab != 'toolbar-stats') ? active_tab : 'toolbar-facebook'));
				}
				
				$("#toolbar .tabs .stats").parent().hide();
				$("#toolbar-stats").hide();
			}
			
			o.resizeFlash = function(size) {
				if( size == "small") {
					swffit.fit("global-map", '800px', '500px');
				} else if( size == "large" ) {
					swffit.fit("global-map", '1024', '500px');
				}
			}

			o.getFlashMovie = function(movieName) {
			  	var isIE = navigator.appName.indexOf("Microsoft") != -1;
			  	return (isIE) ? window[movieName] : document[movieName];
			}
			
			var update_page = function(){
				$(".loading_small").show();
				if (ajax_loading){
					$.each(ajax_requests,function(k,v){
						v.abort();
						if ((k+1) == ajax_requests.length){
							update_ajax_data();
						}
					});
				}
				else {
					update_ajax_data();
				}
			};

			function objToString (obj) {
				var str = '';
				for (var p in obj) {
					if (obj.hasOwnProperty(p)) {
						str += p + '::' + obj[p] + '\n';
					}
				}
				return str;
			}


			var update_ajax_data = function(){
				ajax_loading = true;
				ajax_data = {};
				ajax_requests = [];
			
				if (ajax_updates.indexOf('stories') != -1 || ajax_updates.indexOf('top_recent_stories') != -1) {
					ajax_requests.push($.ajax({
						url: ajax_urls['get-stories'],
						type: 'POST',
						data: {updates: ajax_updates,params: url_params},
						success: function(data){
				
							try{
								data = $.parseJSON(data);
								
								ajax_data['stories'] = data['stories'];
								ajax_data['story_id_to_key'] = data['story_id_to_key'];
								ajax_data['top_stories'] = data['top_stories'];
								ajax_data['recent_stories'] = data['recent_stories'];
								ajax_data['counter'] = data['counter'];
							}
							catch(err){

							}
		
						}
					})
					);
				}
			
				if (ajax_updates.indexOf('tags') != -1) {
					ajax_requests.push($.ajax({
						url: ajax_urls['generate-tags-list'],
						data: url_params,
						type: 'POST',
						success: function(data){
							ajax_data['tags'] = data;
						}
					})
					);
				} 
				if (ajax_updates.indexOf('producers') != -1) {
					ajax_requests.push($.ajax({
						url: ajax_urls['generate-producers-list'],
						data: url_params,
						type: 'POST',
						success: function(data){
							ajax_data['producers'] = data;
						}
					})
					);
				} 
			};
			
			var update_history = function(event){
				var path = stories_url;
				var url_params_parts = [];

				$.each(url_params,function(k,v){
					url_params_parts.push(k+'='+v);
				});
				
				path += url_params_parts.join('&');

				if (event !== null) {
					event.preventDefault();
				}
				History.pushState(null, null, path);
				_gaq.push(['_trackPageview',path]);

				$(window).on("popstate",function(event){
					//window.location.href = '';
				});
				
		
				/*if (typeof(window.history.pushState) == 'function') {
				    window.history.pushState(null,path,path);
				} else {
				    window.location.hash = '#!'+path;
				}*/
			};

			var update_url_param = function(name,value,value_type,action){
				switch (value_type){
					case 'string':
						if (action == 'add') {
							url_params[name] = String(value);
						}
						else{
							delete url_params[name];
						}
						break;
					case 'comma_separated_string':
						if (typeof url_params[name] == 'undefined') {
							url_params[name] = [];
						}
						else {
							url_params[name] = String(url_params[name]).split(',');
						}

						if (action == 'add') {
							url_params[name].push(value);
						}
						else {
							var k = url_params[name].indexOf(value);
							url_params[name].splice(k,1);
						}

						if (url_params[name].length > 0) {
							url_params[name] = url_params[name].join(',');
						}
						else {
							delete url_params[name];
						}
						break;
				}
			};
			
			var all_on = function(event){
				o.selectWorldMap();
				
				if (typeof url_params['cio'] != 'undefined'){
					$("#story-navigation ul a.inactive").removeClass('inactive');
                                      //TMB: Add it because the categories have been reset 
                                        $("#category_reset").addClass('inactive');
				}
				if (typeof url_params['otio'] != 'undefined'){
					$("#filter-corp input[type=checkbox]").attr('checked',true);
				}
				if (typeof url_params['mio'] != 'undefined'){
					$("#filter-format input[type=checkbox]").attr('checked',true);
					$("#filter-format input[type=checkbox]").parent().find('a').addClass('checkbox-checked');
					$("#filter-format input[type=checkbox]").parent().addClass('status-checked');
				}
				if (typeof url_params['duration'] != 'undefined'){
					$("#filter-duration .status-checked").removeClass('status-checked');
					$("#filter-duration").find('li:last').addClass('status-checked');
					$("#filter-duration .ui-slider-handle").html($("#filter-duration").find('li:last input').attr('title'));
					$("#filter-duration .ui-slider-handle").css('left','100%');
				}
				if (typeof url_params['adult_content'] != 'undefined'){
					$("#filter-adult input[type=radio]:first").attr('checked',true);
				}
				if (typeof url_params['dates'] != 'undefined'){
					$("#filter-dates").find('.date-year-from').val($("#filter-dates").attr('data-min'));
					$("#filter-dates .ui-slider-handle").html($("#filter-dates").attr('data-min'));
					$("#filter-dates .ui-slider-handle").css('left','0%');
				}
				if (typeof url_params['search'] != 'undefined'){
					$("#filter-search input[name=search]").val('');
				}
			}
			
			var site_name_click_handler = function(event){
				all_on();
				url_params = {};
				
				$("#filter-friends").children().find('li').removeClass("active");
				$("#filter-friends").children().find('a').removeClass("active");
				$(".current_user_views").removeClass("active");
				$(".current_user_recommends").removeClass("active");
				$("#user-info").removeClass("active");
	  
				
				ajax_updates = ['stories','top_recent_stories','tags','producers'];
				update_page();
				update_history(event);
				
				select_all_handler();
			};
			
			var category_button_click_handler = function(event){
				var category_id = this.id.replace('category_','');
					
				if ($(this).hasClass('inactive')) {
					update_url_param('cio',category_id,'comma_separated_string','remove');
				}
				else {
					update_url_param('cio',category_id,'comma_separated_string','add');
				}
      

				ajax_updates = ['stories','top_recent_stories','tags','producers'];
				update_page();
				update_history(event);
				
				select_all_handler();
			};
			
	
			var user_map_click_handler = function(event, type){
		
                           	//TMB - The click event is now against an anchor so need to prevent default.
                                event.preventDefault();
                                
                                var el = $(event.target);
                                
                                // eg: user_views
							    var user_id = event.target.id.replace(type + '_','');
                                //var parent_li = el.parents('li');
								
								var parent_li = el.parents('li, #user-info');
                                
                                //strip out the _
                                var paramName = type.replace('_','');
                                
                                 // Remove both sets from the URL.
                                update_url_param('userviews',user_id,'string','remove');
                                update_url_param('userrecommends',user_id,'string','remove');
								
	
								delete url_params['tags'];
								delete url_params['producers'];
								
					
                                // Has current link already been ticked?
                                if(el.hasClass('active')){

                                        // TMB remove the active class of all sibling LIs
                                        el.parents('ul').children().removeClass("active");
                                        el.parents('ul').children().find('a').removeClass("active");
										el.removeClass("active");
										$("#user-info").removeClass("active");
								
                                
                                    }else{
                                        // TMB remove the active class of all sibling LIs
                                        el.parents('ul').children().removeClass("active");
                                        el.parents('ul').children().find('a').removeClass("active");
										$("#filter-friends").children().find('li').removeClass("active");
										$("#filter-friends").children().find('a').removeClass("active");
										
										if (el.hasClass(".current_user_views")){
											$(".current_user_recommends").removeClass("active");
										}
										else if (el.hasClass(".current_user_recommends")){
											$(".current_user_recommends").removeClass("active");	
										}
										else{
											$(".current_user_recommends").removeClass("active");
											$(".current_user_views").removeClass("active");
											$("#user-info").removeClass("active");
										}
                                        
                                        parent_li.addClass('active');
                                        el.addClass('active');
                                        update_url_param(paramName,user_id,'string','add');
                                    }
                                    
				ajax_updates = ['stories','top_recent_stories','tags','producers'];
				update_page();
				update_history(event);
				
				select_all_handler();
			};
			
			$(".user_views").live('click',function(e){user_map_click_handler(e, 'user_views')});
			$(".user_recommends").live('click',function(e){user_map_click_handler(e, 'user_recommends') });
                        
			// END TDM
			

			/*var filter_tags_producers_mouseleave_handler = function(event){
				var has_changes = false;
				var checkbox_values = {'tags': [],'producers': []};
					
				$(this).find('.status-checked').each(function(){
					var checkbox = $(this).find('input[type=checkbox]');
						
					if (!checkbox.hasClass('story-all')) {
						checkbox_values[checkbox.attr('name')].push(checkbox.attr('value'));
					}
				});
				
				checkbox_values['tags'].sort();
				checkbox_values['producers'].sort(function(a,b){return a - b;});
				checkbox_values['tags'] = checkbox_values['tags'].join(',');
				checkbox_values['producers'] = checkbox_values['producers'].join(',');
					
				var url_params_tags = (typeof url_params['tags'] != 'undefined') ? url_params['tags'] : '';
				var url_params_producers = (typeof url_params['producers'] != 'undefined') ? url_params['producers'] : '';
					
				if (url_params_tags != checkbox_values['tags'] || url_params_producers != checkbox_values['producers']) {
					(checkbox_values['tags'] != '') ? url_params['tags'] = checkbox_values['tags'] : delete url_params['tags'];
					(checkbox_values['producers'] != '') ? url_params['producers'] = checkbox_values['producers'] : delete url_params['producers'];
						
					ajax_updates = ['stories'];
					update_page();
					update_history(event);
				}
			};*/
			
			var filter_tags_producers_change_handler = function(elem){
				if (elem.hasClass('story-all')) {

					$("#filter-"+elem.attr('name')).find('.status-checked').not(elem.parent()).find('input').attr('checked',false);
					$("#filter-"+elem.attr('name')).find('.status-checked').not(elem.parent()).find('a').removeClass('checkbox-checked');
					$("#filter-"+elem.attr('name')).find('.status-checked').not(elem.parent()).removeClass('status-checked');
					/* 
					var reset_show_all = true;
					$(".category_button").each(function(i){
							if ($(this).hasClass("inactive")){
								reset_show_all = false;
							}
					});
					if (reset_show_all){
							$("#category_reset").addClass("inactive");
					}
					*/
					delete url_params[elem.attr('name')];
				}
				else {
					
					$("#filter-"+elem.attr('name')).find('.story-all').parent().find('input').attr('checked',false);
					$("#filter-"+elem.attr('name')).find('.story-all').parent().find('a').removeClass('checkbox-checked');
					$("#filter-"+elem.attr('name')).find('.story-all').parent().removeClass('status-checked');
					
					if (elem.parent().hasClass('status-checked')) {
			
						// $("#category_reset").removeClass("inactive");
						update_url_param(elem.attr('name'),elem.val(),'comma_separated_string','add');
					}
					else {
						if ($("#filter-"+elem.attr('name')+" .status-checked").length == 0) {
							$("#filter-"+elem.attr('name')).find('.story-all').parent().find('input').attr('checked',true);
							$("#filter-"+elem.attr('name')).find('.story-all').parent().find('a').addClass('checkbox-checked');
							$("#filter-"+elem.attr('name')).find('.story-all').parent().addClass('status-checked');
						}
						update_url_param(elem.attr('name'),elem.val(),'comma_separated_string','remove');
					}
				}
				select_all_handler();
			};
			
			
			var select_all_handler = function(event){
				var is_inactive = true;
				for (var key in url_params) {
				   var obj = url_params[key];
				   for (var prop in obj) {
					  var val =  obj[prop];
					  if (val!=""){
						is_inactive = false;
					  }
				   }
				}
				if (is_inactive){
					$("#category_reset").addClass("inactive");
				}
				else{
					$("#category_reset").removeClass("inactive");
				}
				
			
			};
			
			var filter_tags_change_handler = function(event){
				filter_tags_producers_change_handler($(this));
				ajax_updates = ['stories','top_recent_stories','producers'];
				update_page();
				update_history(event);
				
				select_all_handler();
			};
			
			var filter_producers_change_handler = function(event){
				filter_tags_producers_change_handler($(this));
				ajax_updates = ['stories','top_recent_stories','tags'];
				update_page();
				update_history(event);
				
				select_all_handler();
			};
			
			var filter_corp_input_change_handler = function(event){
				if ($(this).is(':checked')) {
					update_url_param('otio',$(this).val(),'comma_separated_string','remove');
				}
				else {
					update_url_param('otio',$(this).val(),'comma_separated_string','add');
				}

				ajax_updates = ['stories','top_recent_stories','tags','producers'];
				update_page();
				update_history(event);
			};
			
			var filter_format_input_change_handler = function(event){
				if ($(this).is(':checked')) {
					update_url_param('mio',$(this).val(),'comma_separated_string','remove');
				}
				else {
					update_url_param('mio',$(this).val(),'comma_separated_string','add');
					if ($(this).parent().attr('class').replace('format-','') == 'video') {
						delete url_params['duration'];
					}
				}

				ajax_updates = ['stories','top_recent_stories','tags','producers'];
				update_page();
				update_history(event);
			};
			
			var filter_duration_slidechange_handler = function(event, ui){
				$("#filter-duration .status-checked").removeClass('status-checked');
					
				var li = $("#filter-duration").find('li:nth-child('+(ui.value+1)+')');
				var duration = li.find('input[type=radio]').val();
						
				li.addClass('status-checked');
				url_params_duration = (typeof url_params['duration'] != 'undefined') ? url_params['duration'] : '';
					
				if (url_params_duration != duration) {
					if (duration != '') {
						update_url_param('duration',duration,'string','add');
					}
					else {
						update_url_param('duration',duration,'string','remove');
					}
						
					ajax_updates = ['stories','top_recent_stories','tags','producers'];
					update_page();
					update_history(event);
				}
			};
			
			var filter_adult_input_change_handler = function(event){
				if ($(this).val() != '') {
					update_url_param('adult_content',$(this).val(),'string','add');
				}
				else {
					update_url_param('adult_content',$(this).val(),'string','remove');
				}

				ajax_updates = ['stories','top_recent_stories','tags','producers'];
				update_page();
				update_history(event);
			};
			
			var filter_dates_slidechange_handler = function(event, ui){
				$("#filter-dates input[name=date-from]").val(ui.value);
				var dates = ui.value+','+$("#filter-dates").attr('data-max');
				url_params_dates = (typeof url_params['dates'] != 'undefined') ? url_params['dates'] : '';
					
				if (url_params_dates != dates) {
					if (ui.value != $("#filter-dates").attr('data-min')) {
						update_url_param('dates',dates,'string','add');
					}
					else {
						update_url_param('dates',dates,'string','remove');
					}
						
					ajax_updates = ['stories','top_recent_stories','tags','producers'];
					update_page();
					update_history(event);
				}
			};
			var filter_search_keypress_handler = function(event){
				$(this).unbind('keypress');
				
				var code = (event.keyCode ? event.keyCode : event.which);
				var search_value = $(this).val().trim();
				var search_items = function(){
					var search_value = $("#filter-search input[name=search]").val().trim();
					
					if (search_value != '') {
						update_url_param('search',search_value,'string','add');
					}
					else {
						update_url_param('search',search_value,'string','remove');
					}
					
					ajax_updates = ['stories','top_recent_stories','tags','producers'];
					update_page();
					update_history(event);
					$("#filter-search input[name=search]").bind('keypress',filter_search_keypress_handler);
				};
				
				var log_search_term = function(){
					var search_value = $("#filter-search input[name=search]").val().trim();
					
					if (search_value != '') {
						$.ajax({
							url: ajax_urls['log-search-term'],
							data: {term: search_value},
							type: 'POST',
							success: function(data){}
						});
					}
				};
				
				if (code == '13') {
					search_items();
					log_search_term();
				}
				else {
					setTimeout(search_items,1500);
					setTimeout(log_search_term,3000);
				}
			};
			
			var load_stats = function(){
				var stats_subcategory_change_handler = function(){
					var select = $(this);
					
					if (select.val() != '') {
						$.ajax({
							url: ajax_urls['generate-chart-data'],
							data: {indicator: select.val(),country: url_params['country']},
							success: function(data){
								$("#stats-info").html('');
								data = $.parseJSON(data);
								
								if (typeof data['error'] != 'undefined') {
									$("#stats-info").html(data['error']);
								}
								else {
									var fill_first_column = function(){
										$("#stats-info .highcharts-series rect:nth-child(4)").attr('fill','#FFFFFF');
										$("#stats-info .highcharts-tracker rect:nth-child(1)").attr('fill','rgb(255,255,255)');
									};
									
									var chart = new Highcharts.Chart({
										chart: {
											renderTo: 'stats-info',
											defaultSeriesType: 'column',
											width: 207,
											height:$('#page').height()-350,
											backgroundColor: '#222222',
											events: {
												load: function(){
													//fill_first_column();
												}
											}
										},
										title: {
											text: ''
										},
										xAxis: {
											categories: data['countries'],
											lineColor: '#656565',
											tickWidth: 0,
											labels: {
												rotation: -90,
												align: 'right',
												style: {
													 font: '11px DINWeb-Bold,Arial,Helvetica,sans-serif'
												}
											}
										},
										yAxis: {
											min: 0,
											title: {
												text: ''
											},
											lineWidth: 1,
											lineColor: '#656565',
											gridLineWidth: 0,
											labels: {
												style: {
													 font: '11px DINWeb-Bold,Arial,Helvetica,sans-serif'
												}
											}
										},
										plotOptions: {
											column: {
												borderWidth: 0,
												color: '#656565'
											}
										},
										tooltip: {
											borderColor: '#656565',
											style: {
												 font: '93.75%/1.25 DINWeb-Bold,Arial,Helvetica,sans-serif'
											},
											formatter: function() {
												return '<b>'+ this.x +'</b><br/>'+
													Highcharts.numberFormat(this.y, 1);
											}
										},
									    series: [{
											name: select.html(),
											data: data['values']		
										}],
										legend: {
											enabled: false
										},
										credits: {
											enabled: false
										}
									});
								}
							}
						});
					}
					else {
						$("#stats-info").html('<img src="/images/stats_info.jpg" />');
					}
				};
				
				if (!($("#stats-chooser .stats-category select").length > 0)) {
					$.ajax({
						url: ajax_urls['generate-list'],
						data: {type: 'select',name: 'stats-category',first_label: '- Please select category -',selected: '',model: 'Stats',method: 'getStats',params: {parent: 0,state: 1}},
						success: function(data){
							$("#stats-chooser .stats-category").append(data);
							
							jQuery('#stats-chooser .stats-category select')
								.bind('selectopen', GlobalStories.Map.Stats.hideStats)
								.bind('selectclose', GlobalStories.Map.Stats.showStats)
								.JSizedFormSelect();
							
							var stats_category_change_handler = function(){
								$("#stats-chooser .stats-subcategory").children().not('label').remove();
								
								if ($(this).val() != '') {
									$.ajax({
										url: ajax_urls['generate-list'],
										data: {type: 'select',name: 'stats-subcategory',first_label: '- Please select subcategory -',selected: '',value_field: 'indicator',model: 'Stats',method: 'getStats',params: {parent: $(this).val(),state: 1}},
										success: function(data){
											$("#stats-chooser .stats-subcategory").append(data);
											
											jQuery('#stats-chooser .stats-subcategory select')
												.bind('selectopen', GlobalStories.Map.Stats.hideStats)
												.bind('selectclose', GlobalStories.Map.Stats.showStats)
												.JSizedFormSelect();
											
											$("#stats-chooser .stats-subcategory select").bind('change',stats_subcategory_change_handler);
										}
									});
								}
								else {
									$("#stats-info").html('<img src="/images/stats_info.jpg" />');
								}
							};
							
							$("#stats-chooser .stats-category select").bind('change',stats_category_change_handler);
						}
					});
				}
				else {
					$("#stats-chooser .stats-subcategory select").trigger('change');
				}
			};
			
			var top_recent_stories_hover_in = function(){
				try {
					o.selectStory(this.id.replace('item_',''));
				}
				catch (e) {}
			};
			
			var top_recent_stories_hover_out = function(){
				try {
					o.selectStory(null);
				}
				catch (e) {}
			};
			
			$(document).ajaxStop(function(){
				ajax_loading = false;
				if (!$.isEmptyObject(ajax_data)) {
					if (ajax_updates.indexOf('stories') != -1 && typeof ajax_data['stories'] != 'undefined') {
						stories = ajax_data['stories'];
						story_id_to_key = ajax_data['story_id_to_key'];
						//TMB : console.log('Send data to flash' +  $.toJSON(ajax_data['stories']) );
						o.getFlashMovie("global-map").sendMapData($.toJSON(ajax_data['stories']));
					}
					if (ajax_updates.indexOf('top_recent_stories') != -1 && typeof ajax_data['top_stories'] != 'undefined' && typeof ajax_data['recent_stories'] != 'undefined') {
						$("#stories-top").html(ajax_data['top_stories']);
						$("#stories-recent").html(ajax_data['recent_stories']);
						$("#stories li").hover(top_recent_stories_hover_in,top_recent_stories_hover_out);
						jQuery(window).trigger('resize');
					}
					if (ajax_updates.indexOf('tags') != -1 && typeof ajax_data['tags'] != 'undefined') {
						$("#filter-tags ul").parent().html(ajax_data['tags']);
						
						$('#filter-tags input[type="checkbox"]')
							.JSizedFormCheckbox()
							.maintainParentCheckedClass('status-checked');
						GlobalStories.Shared.styledForm();
						GlobalStories.Map.Filter.redrawScroller();
						$("#filter-tags input").bind('change',filter_tags_change_handler);
					}
					if (ajax_updates.indexOf('producers') != -1 && typeof ajax_data['producers'] != 'undefined') {
						$("#filter-producers ul").parent().html(ajax_data['producers']);
						
						$('#filter-producers input[type="checkbox"]')
							.JSizedFormCheckbox()
							.maintainParentCheckedClass('status-checked');
						GlobalStories.Shared.styledForm();
						GlobalStories.Map.Filter.redrawScroller();
						$("#filter-producers input").bind('change',filter_producers_change_handler);
					}
					
					if (typeof ajax_data['counter'] != 'undefined') {
						$.each(ajax_data['counter'],function(k,v){
							switch (k) {
								case 'now_showing_stories': $("#filter-info .now_showing_number_of_stories").html(v); break;
								case 'tags_stories': $("#filter-tags .now_showing_number_of_stories").html(v); break;
								case 'producers_stories': $("#filter-producers .now_showing_number_of_stories").html(v); break;
								case 'type': $("#filter-info .counter_type").html(v); break;
							}
						});
					}
	
					ajax_data = {};
					$(".loading_small").hide();

				}
			});
			
			$(document).ready(function(){				
				var wait_flash_fully_loaded = setInterval(function(){
					if (typeof $("#global-map")[0].sendMapData == 'function') {
						clearInterval(wait_flash_fully_loaded);

						//TMB: console.log('Send data to flash' +  $.toJSON(stories) );
						
						o.getFlashMovie("global-map").sendMapData($.toJSON(stories));
	
						if (typeof url_params['country'] != 'undefined') {
							o.selectCountry(url_params['country']);
						}
						else {
							$("#toolbar .tabs .stats").parent().hide();
							$("#toolbar-stats").hide();
						}
							
						$("#stories .items li").hover(top_recent_stories_hover_in,top_recent_stories_hover_out);
							
						$(".loading_small").hide();
					}
				},1);

                // TMB: Added the same click event to the category reset button 
				$("#header .site-name a, #category_reset").bind('click',site_name_click_handler);
				$(".category_button").bind('click',category_button_click_handler);
				/*$("#filter-tags-producers").bind('mouseleave',filter_tags_producers_mouseleave_handler);*/
				$("#filter-tags input").bind('change',filter_tags_change_handler);
				$("#filter-producers input").bind('change',filter_producers_change_handler);
				$("#filter-corp input[type=checkbox]").bind('change',filter_corp_input_change_handler);
				$("#filter-format input[type=checkbox]").bind('change',filter_format_input_change_handler);
				$("#filter-duration").bind('slidechange',filter_duration_slidechange_handler);
				$("#filter-adult input[type=radio]").bind('change',filter_adult_input_change_handler);
				$("#filter-dates").bind('slidechange',filter_dates_slidechange_handler);
				$("#filter-search input[name=search]").bind('keypress',filter_search_keypress_handler);
				
				if (active_footer_menu != '') {
					$("#"+active_footer_menu).trigger('click');
				}
				
				select_all_handler();
					
			});
