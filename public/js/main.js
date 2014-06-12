(function () {

	// Fix for weird Safari Repaint issue
	jQuery(document.body).delegate('.scrollbar-container', 'jsp-scroll-y', function (e) {
		this.style.display='none';
		this.offsetHeight; // no need to store this anywhere, the reference is enough
		this.style.display='block';
	});

	jQuery.fn.maintainParentCheckedClass = function maintainCheckedClass(className) {
		return this.each(function () {
			jQuery(this).parent().toggleClass(className, this.checked);
		});
	};

	window.GlobalStories = {};
	
	GlobalStories.init = function init () {
		GlobalStories.Shared.init();
		GlobalStories.Map.init();
		GlobalStories.Story.init();
	};
	
	GlobalStories.Shared = {
		init: function init() {
			GlobalStories.Shared.customScrollbar('#filter-tags .wrapper, #filter-producers .wrapper, #filter-friends .wrapper, #filter-community .wrapper, .scrollbar-test');
			GlobalStories.Shared.tabs();
			GlobalStories.Shared.overlay();
			GlobalStories.Shared.styledForm();
		},

		customScrollbar: function customScrollbar(elements) {
			jQuery(elements).each(function(){
				var elm = jQuery(this),
					container = null;
				
				elm.wrapInner('<div class="scrollbar-container"></div>');
				container = elm.find('.scrollbar-container');

				container.height(elm.height())
						 .jScrollPane({
							showArrows: false,
							verticalGutter: 6
						 })
						 .append('<div class="scrollbar-fade"></div>');
			});
		},

		tabs: function tabs() {
			jQuery('ul.tabs').parent().each(function() {
				jQuery(this).tabs({
					fx:[{
						opacity: 'hide',
						duration: 1
					},
					{
						opacity: 'show',
						duration: 500
					}],
					show: GlobalStories.Map.Filter.redrawScroller
				});
			});
		},

		overlay: function() {
			jQuery('#footer a').not('#report-broken-story, #suggest-story, #back-to-story').colorbox({
				ajax: true,
				width: 500,
				height: 542,
				onOpen: function() {
					jQuery('#colorbox').addClass('colorbox-overlay-text');
				},
				onComplete: function() {
					GlobalStories.Shared.customScrollbar('#colorbox .overlay-text .content');
					History.pushState(null, null, $.colorbox.element().attr('href'));
				},
				onClosed: function() {
					History.pushState(null, null, $(".back-to-map").attr('href'));
				}
			});
		},

		styledForm: function styledForm() {
			jQuery('input:checkbox, input:radio')
				.bind('change fakechange.globalstories', GlobalStories.Shared.handleCheckboxRadioChange)
				.each(GlobalStories.Shared.handleCheckboxRadioChange);
				
			jQuery('input[placeholder], textarea[placeholder]').JSizedFormPlaceholder();
		},
		
		handleCheckboxRadioChange: function handleCheckboxRadioChange() {
			jQuery(this)
				.closest('form')
				.find('input[name="' + this.name + '"]')
				.maintainParentCheckedClass('status-checked');
		}
	};
	
	GlobalStories.Map = {
		init: function init() {
			GlobalStories.Map.Flash.init();
			GlobalStories.Map.Facebook.init();
			GlobalStories.Map.Stats.init();
			GlobalStories.Map.Filter.init();
			GlobalStories.Map.Types.init();
			GlobalStories.Map.Carousel.init();
			GlobalStories.Map.Tooltips.init();
		},
		
		Flash: {
			init: function init () {
				swfobject.embedSWF('/assets/swf/main.swf',
								   'map-wrapper',
								   '100%',
								   '100%',
								   '10.0.0', 
								   '/assets/swf/expressInstall.swf', 
								   {basePath: '/'},
								   {scale: 'noScale',salign: 'lt',menu: 'false',bgcolor: '#28211C',allowfullscreen: 'true',allowscriptaccess: 'always',wmode: 'opaque'},
								   {id: 'global-map',name: 'global-map'}
								  );
			},

			// Stub to update the SWF to show selected story types
			updateStoryTypes: function updateStoryTypes(types) {
				try {
					console.log('Updating Flash types', types);

					jQuery('#map').flash(function () {
						this.doSomething(types);
					});
				}
				catch(e) {}
			},
			
			// Stub to update the SWF to search for stories
			storySearch: function storySearch(search) {
				/*try {
					console.log('Doing Story search', search);

					jQuery('#map').flash(function () {
						this.doSomething(search);
					});
				}
				catch(e) {}*/
			}
		},
		
		Stats: {
			init: function init() {
				/*jQuery('#stats-chooser .stats-category select')
					.bind('selectopen', GlobalStories.Map.Stats.hideStats)
					.bind('selectclose', GlobalStories.Map.Stats.showStats)
					.JSizedFormSelect();*/
			},
			
			hideStats: function hideStats(event, instance) {
				jQuery('#stats-info img').hide();
			},
			
			showStats: function showStats(event, instance) {
				jQuery('#stats-info img').show();
			}
		},
		
		Facebook: {
			init: function init() {
				/*FB.init({appId: '186638778080440', status: true, cookie: true});
				
				jQuery('#facebook-login').click(GlobalStories.Map.Facebook.handleClick);*/
				
				this.setLayout();
			},
			
			setLayout: function setLayout() {
				jQuery(window).bind('resize', GlobalStories.Map.Facebook.resizeLayout)
			},

			resizeLayout: function resizeLayout() {
				var elm = jQuery('#filter-community, #filter-friends');				
                                //TMB: setting constants for maths
                                // Also corrected the heights for more accure scrolling layout
				var value = 0, minHeight = 728, toolBarHeight = 460;
				
				/* TMB: This now references the parent holder and wrapper div, instead of #user-stories .section
                                 *
                                 */
                                
                                if( $(window).height() > minHeight) {
					value = ($(window).height() - toolBarHeight);
					elm.find('.wrapper').css('height', value + 'px');
				} else {
					value = (minHeight - toolBarHeight);
					elm.find('.wrapper').css('height', value + 'px');
				}
				
				//TMB: No longer required
                                //jQuery('.facebook-signedin').find('.promote').css('margin-top', value+50 + "px" );
				
				GlobalStories.Map.Filter.redrawScroller();				
				
			},
			
			
			handleClick: function handleClick(event) {
				var config = {
						method: 'feed',
						link: document.location.href,
						picture: 'http://lemurking.files.wordpress.com/2008/03/hamster.jpg',
						name: 'Global Stories',
						caption: 'This is a confused hamster',
						description: 'This hamster is confused, can you help him?'
					};
				FB.ui(config, GlobalStories.Map.Facebook.handleResponse);
			},
			
			handleResponse: function handleResponse(response) {
				// The useful data here is response.post_id
			}
		},
		
		Filter: {
			init: function init() {
				
				jQuery('#toolbar .tabs a').click(GlobalStories.Map.Filter.toggle);
				
				jQuery('#toolbar, #filter-tags-producers, #filter-friends-community')
					.bind('tabsshow', GlobalStories.Map.Filter.redrawScroller);
				
				jQuery('#filter-dates').each(function () {
					new GlobalStories.Map.Filter.DatesSlider(this);
				});
				
				jQuery('#filter-duration').each(function () {
					new GlobalStories.Map.Filter.DurationSlider(this);
				});
				
				jQuery('#filter-format .format-video input, #filter-format .format-audio input')
					.bind('click change', GlobalStories.Map.Filter.toggleDuration);
					//.each(GlobalStories.Map.Filter.toggleDuration);

				jQuery('#filter-tags-producers input[type="checkbox"], #filter-friends-community input[type="checkbox"], #filter-format input[type="checkbox"]')
					.JSizedFormCheckbox()
					.maintainParentCheckedClass('status-checked');
				
				/*$('#filter-tags-producers input[type="checkbox"]')
					.filter('.story-all')
					.bind('change', GlobalStories.Map.Filter.selectAll)
					.end()
					.not('.story-all')
					.bind('change', GlobalStories.Map.Filter.selectIndividual);*/
				
				jQuery('#filter-search input')
					.bind('keyup', GlobalStories.Map.Filter.handleSearch);
					
				//GlobalStories.Map.Filter.setupAutoClose();
				GlobalStories.Map.Filter.setLayout();
			},
			
			toggle: function toggle() {
				var toolbar = jQuery('#toolbar'),
					open = toolbar.data('open');
				
				if (!toolbar.is(':animated')) {
					if (open !== this) {
						GlobalStories.Map.Filter.show();
						toolbar.data('open', this);
					}
					else {
						GlobalStories.Map.Filter.hide();
					}
					
	        		if (window.PIE) {
						toolbar.find('> .tabs css3-container').css({zIndex: 1});
					}
				}
			},
			
			show: function show(tab) {
				var toolbar = jQuery('#toolbar');
					

				toolbar
					.animate({left: 0}, function(){
						jQuery('#map').css({left: toolbar.width() + 'px'});
					})
					.addClass('toolbar-expanded');


				if (tab) {
					jQuery('#toolbar').tabs('select', tab);
				}
				
				jQuery('#toolbar').data('open', true);
									
			},
			
			setupAutoClose: function setupAutoClose() {
				var timeout = 0;
				jQuery('#toolbar').hover(
					function () {
						window.clearTimeout(timeout);
						timeout = 0;
					},
					function () {
						if (!timeout) {
							timeout = window.setTimeout(GlobalStories.Map.Filter.hide, 10000);
						}
					}
				);
			},
			
			hide: function hide() {
				var toolbar = jQuery('#toolbar'),
					width = toolbar.width();
				jQuery('#map').css({left: 0});
				toolbar
					.animate({left: width * -1 + 'px'})
					.removeClass('toolbar-expanded')
					.data('open', false);
			},
			
			toggleDuration: function toggleDuration(event) {
				if ( (!jQuery("#filter-format .format-video").hasClass('status-checked') && !jQuery("#filter-format .format-audio").hasClass('status-checked')) || (jQuery("#filter-format .format-video").hasClass('status-checked') && !jQuery('#filter-duration').is(':visible')) || (jQuery("#filter-format .format-audio").hasClass('status-checked') && !jQuery('#filter-duration').is(':visible')) ){
					window.setTimeout(function () {
						jQuery('#filter-duration').slideToggle(this.checked);
					}.bind(this), 50);		
				}
			},
			
			handleSearch: function handleSearch(event) {
				window.clearTimeout(GlobalStories.Map.Filter.handleSearch.timeout);
				
				GlobalStories.Map.Filter.handleSearch.timeout = window.setTimeout(function () {
					GlobalStories.Map.Flash.storySearch(jQuery('#filter-search input').val());
				}, 500);
			},
			
			setActiveTag: function setActiveTag(tag) {
		
				var selector = '.story-all, .story-tag-' + tag,
					checkboxes = jQuery('#filter-tags input[type="checkbox"]');
				
				checkboxes
					.not(selector)
					.removeAttr('checked')
					.trigger('change')
					.maintainParentCheckedClass('status-checked');

				checkboxes
					.filter(selector)
					.attr('checked', 'checked')
					.trigger('change')
					.maintainParentCheckedClass('status-checked');
			},

			redrawScroller: function redrawScroller() {
				jQuery('#filter-tags, #filter-producers, #filter-friends, #filter-community').each(function () {
					var $this = jQuery(this);
					if ($this.is(':visible')) {
						var container = $this.find('.scrollbar-container'),
							newHeight = container.parent().height();
						
                                                //TMB: Check to see if there is actaully a scrollbar
                                                if(newHeight != null){
						container.height(newHeight)
								 .jScrollPane({
									showArrows: false,
									verticalGutter: 6
								 });
                                                                 };
					}
				});
			},

			setLayout: function setLayout() {
				var elm = jQuery('#toolbar-filters');

				elm.addClass('ui-layout')
				   .find('.footer').bind('resize', GlobalStories.Map.Filter.resizeLayout);

				jQuery(window).bind('resize', GlobalStories.Map.Filter.redrawScroller)
			},

			resizeLayout: function resizeLayout() {
				var elm = jQuery('#toolbar-filters');
				elm.find('.content').css('bottom', elm.find('.footer').height() + 20 + 'px');
				GlobalStories.Map.Filter.redrawScroller();				
				
			},
			
			selectAll: function selectAll() {
				if (this.checked) {
					jQuery(this.form).find('input[name="' + this.name + '"]').not(this).removeAttr('checked').trigger('change');
				}
			},
			
			selectIndividual: function selectIndividual() {
				if (this.checked) {
					jQuery(this.form).find('input[name="' + this.name + '"].story-all').removeAttr('checked').trigger('change');
				}
			}
		},
		
		Types: {
			init: function init() {
				jQuery('#story-navigation').delegate('a:not(#category_reset)', 'click', GlobalStories.Map.Types.handleClick);
				//GlobalStories.Map.Types.update(false);
			},
			
			handleClick: function handleClick(event) {
				jQuery(event.currentTarget).toggleClass('inactive');
				GlobalStories.Map.Types.update();
			},
			
			update: function update(repaint) {
				repaint = typeof repaint === 'undefined' ? true : repaint;
				var types = jQuery('#story-navigation a').not('.inactive').collectAttr('data-story-type'),
					selector = types.length ? ('.story-all, .story-' + types.join(', .story-')) : '.story-all',
					checkboxes = jQuery('#filter-tags input[type="checkbox"], #filter-producers input[type="checkbox"]');
					
				//checkboxes.not(selector).parent().hide().find('input').attr('disabled', 'disabled');
				//checkboxes.filter(selector).parent().show().find('input').removeAttr('disabled');
				
				
				//GlobalStories.Map.Filter.redrawScroller();
				//GlobalStories.Map.Flash.updateStoryTypes(types);
			},
			
			enableAll: function enableAll() {
				jQuery('#story-navigation a').removeClass('inactive');
				GlobalStories.Map.Types.update();
			}
		},
		Carousel: {
			init: function init(element) {
				/*jQuery('#stories')
					.bind('tabsload', GlobalStories.Map.Carousel.setup)
					.delegate('a.next', 'click', GlobalStories.Map.Carousel.next)
					.delegate('a.prev', 'click', GlobalStories.Map.Carousel.prev)
					.delegate('a:not(.next, .prev)', 'click', GlobalStories.Map.Carousel.handleStoryClick);*/
				jQuery('#stories')
					.delegate('a.next', 'click', GlobalStories.Map.Carousel.next)
					.delegate('a.prev', 'click', GlobalStories.Map.Carousel.prev)
					.delegate('a:not(.next, .prev)', 'click', GlobalStories.Map.Carousel.handleStoryClick);
				
				jQuery(window).bind('resize', GlobalStories.Map.Carousel.handleWindowResize);
				jQuery(window).trigger('resize');
			},
			
			handleWindowResize: function handleWindowResize(event) {
				jQuery('#stories .stories-browser').each(function () {
					GlobalStories.Map.Carousel.setup(event, {panel: this});
				});
			},
			
			setup: function setup(event, ui) {
				var items = jQuery('.items', ui.panel),
					slides = items.find('li'),
					visible = Math.floor(items.innerWidth() / slides.first().outerWidth(true));
				if (visible === slides.length && items.data('scroller') === true) {
					items.parent().find('> a.prev, > a.next').remove();
					items.data('scroller', false);
					items.find('ul').css({left: 0});
					slides.removeClass('current').first().addClass('current');
				}
				if (visible < slides.length && items.data('scroller') !== true) {
					items.data('scroller', true);
					items.parent().prepend('<a class="prev" href="#">Previous</a><a class="next" href="#">Next</a>');
					slides.first().addClass('current');
					GlobalStories.Map.Carousel.toggleButtons();
				}
			},
			
			next: function next(event) {
				GlobalStories.Map.Carousel.move(this, 'next');
			},
			
			prev: function prev(event) {
				GlobalStories.Map.Carousel.move(this, 'prev');
			},
			
			toggleButtons: function toggleButtons() {
				var items = jQuery('#stories .stories-browser:not(.ui-tabs-hide) .items'),
					slides = items.find('li'),
					visible = Math.floor(items.innerWidth() / slides.first().outerWidth(true)),
					current = slides.filter('.current'),
					canMoveLeft = current.prev().length === 1,
					canMoveRight = slides.length > slides.index(current) + visible;

				jQuery('#stories a.next').toggleClass('disabled', !canMoveRight);
				jQuery('#stories a.prev').toggleClass('disabled', !canMoveLeft);
			},
			
			move: function move(button, direction) {
				var step = 4,
					items = jQuery(button).siblings('.items'),
					slides = items.find('li'),
					visible = Math.floor(items.innerWidth() / slides.first().outerWidth(true)),
					current = slides.filter('.current');
					//moveTo = current[direction](),

				if (direction == 'next') {
					var moveToIndex = current.index()+1+step;
					var difference = slides.length-visible-moveToIndex+1;
					(difference < 0) ? moveToIndex += difference : '';
				}
				else {
					var moveToIndex = current.index()+1-step;
					(moveToIndex <= 0) ? moveToIndex = 1 : '';
				}

				var moveTo = items.find('li:nth-child('+moveToIndex+')'),
					left = moveTo.length ? (moveTo.position().left * -1) : false;

				if (left !== false && slides.length - slides.index(moveTo) >= visible) {
					items.find('ul').animate({left: left + 'px'}, function () {
						current.removeClass('current');
						moveTo.addClass('current');
						GlobalStories.Map.Carousel.toggleButtons();
					});
				}
			},
			
			handleStoryClick: function (event) {
				$(".back-to-map").attr('href',window.location.pathname);
				$("#back-to-story").hide();
				
				event.preventDefault();
				History.pushState(null, null, event.currentTarget.href);
				_gaq.push(['_trackPageview',event.currentTarget.pathname]);
			}
		},
		
		Tooltips: {
			init: function init() {
				jQuery('.tooltip').parent().one('mouseenter', function () {
					jQuery('.tooltip').remove();
				});
			}
		
		}
	};

	GlobalStories.Map.Filter.DatesSlider = function DatesSlider(element, options) {
		this.element = jQuery(element);

		this.options = jQuery.extend(true, {}, this.options, options || {});
		
		this.slider = jQuery('<div class="slider"></div>').appendTo(this.element);
		
		this.element.addClass('slider-enabled');
		
		this.min = this.element.data('min');
		this.max = this.element.data('max');
		
		this.value = this.element.find(this.options.selectors.year.from).val();
		
		this.slider.slider({
			min: this.min,
			max: this.max,
			step: 1,
			value: this.value,
			slide: this.handleSlide.bind(this)
		});
		
		this.handle = this.element.find(this.options.selectors.slider.handle);
		this.updateHandle(this.value);
	};

	GlobalStories.Map.Filter.DatesSlider.prototype.options = {
		selectors: {
			year: {
				from: '.date-year-from'
			},
			slider: {
				handle: '.ui-slider-handle'
			}
		}
	};
	
	GlobalStories.Map.Filter.DatesSlider.prototype.handleSlide = function handleSlide(event, ui) {
		this.updateHandle(ui.value);
	};
	
	GlobalStories.Map.Filter.DatesSlider.prototype.updateHandle = function updateHandle(value) {
		this.handle.text(value);
	};

	GlobalStories.Map.Filter.DurationSlider = function DurationSlider(element, options) {
		this.element = jQuery(element);

		this.options = jQuery.extend(true, {}, this.options, options || {});
		
		this.slider = jQuery('<div class="slider"></div>').appendTo(this.element);
		
		this.element.addClass('slider-enabled');
		
		this.inputs = this.element.find('input');
		
		this.titles = this.inputs.collectAttr('title');

		this.slider.slider({
			value: this.inputs.filter(':checked').parent().index(),
			min: 0,
			max: this.titles.length - 1,
			slide: this.handleSlide.bind(this)
		});
		
		this.handle = this.element.find(this.options.selectors.slider.handle);
		this.updateHandle(this.inputs.index(this.inputs.filter(':checked')));
	};

	GlobalStories.Map.Filter.DurationSlider.prototype.options = {
		selectors: {
			slider: {
				handle: '.ui-slider-handle'
			}
		}
	};

	GlobalStories.Map.Filter.DurationSlider.prototype.handleSlide = function handleSlide(event, ui) {
		this.updateHandle(ui.value);
	};
	
	GlobalStories.Map.Filter.DurationSlider.prototype.updateHandle = function updateHandle(index) {
		this.handle.text(this.titles[index]);
	};

	GlobalStories.Story = {

		init: function init() {
			
			History.Adapter.bind(window, 'statechange', GlobalStories.Story.handleStateChange);
			GlobalStories.Story.handleStateChange();
			
			GlobalStories.Story.twitterWidget();
                        
			jQuery('#header .back-to-map').click(function (event) {
				$("#back-to-story").attr('href',window.location.pathname);
				$("#back-to-story").show();
				
				event.preventDefault();
				History.pushState(null, null, this.href);
			});
			
			jQuery('#back-to-story').click(function (event) {
				$(this).hide();
				
				event.preventDefault();
				History.pushState(null, null, this.href);
			});
			
			jQuery('#story-pages').delegate('dd.tags span', 'click', GlobalStories.Story.handleTagClick);

			jQuery(window).resize(function(){
				if (jQuery('body').hasClass('story-view')) {
					var container = jQuery('#pages');
					container.css('top', - container.height() / 2 + 'px');

					GlobalStories.Story.resizePanelLayout();
				}
			});
		},

		twitterWidget: function twitterWidget() {
			var action = null,
				selector = '.story-twitter .twtr-bd',
				elm = null;

			action = window.setInterval(function() {
				if (!elm) {
					elm = jQuery(selector);
				} else {
					window.clearInterval(action);
				}
			}, 1000);
		},

		handleStateChange: function handleStateChange() {
			if (jQuery("#story-pages .current .preview-video").length > 0) {
				var clone = jQuery("#story-pages .current .preview-video").clone(true);
				jQuery("#story-pages .current .preview-video").remove();
				jQuery("#story-pages .current .story-media").append(clone);
			}

	        var state = History.getState(); // Note: We are using History.getState() instead of event.state
	        
	        if (state.url.match(/\/story\//gi)) {
	        	GlobalStories.Story.show(state.url);
	        }
	        else {
				GlobalStories.Story.hide(function () {
					jQuery(document.body).addClass('map-view');
					
					if (jQuery("#back-to-story").attr('href') != '') {
						jQuery("#back-to-story").show();
					}
					
					_gaq.push(['_trackPageview',state.hash]);
				});
	        }

		},

		storyLoaded: function storyLoaded(element) {
			jQuery('.story-twitter div[id]', element).each(function () {
				var widget = new TWTR.Widget({
						version: 2,
						id: this.id,
						type: 'search',
						search: jQuery(this).attr('data-twitter-search'),
						interval: 30000,
						title: '',
						subject: jQuery(this).attr('data-twitter-title'),
						width: 291,
						height: 300,
						theme: {
							shell: {
								background: '#161616',
								color: '#ffffff'
							},
							tweets: {
								background: '#222222',
								color: '#ffffff',
								links: '#666666'
							}
						},
						features: {
							scrollbar: false,
							loop: true,
							live: false,
							behavior: 'default'
						},
						ready: function () {
							// GlobalStories.Shared.customScrollbar('.current .story-twitter .twtr-bd');
							//GlobalStories.Story.initTwitterWidget(element);
						}
					});
				
				jQuery(this).data('twitter-widget', widget);
				
				widget.render().start();
			});			

			jQuery('.story-preview-launch', element).each(function (){
				var el = jQuery(this),
					container = el.parent(),
					config = {
						'width': '100%',
						'height': '100%'
					};
				
				if (container.children('.story-preview-html').length > 0) {
					config.href = false;
					config.html = container.find('.story-preview-html').html();	
				}
				else {
					if (container.hasClass('preview-audio') || container.hasClass('preview-photo') || container.hasClass('preview-interact')) {
						config.iframe = true;
					};		
				}

				if (container.hasClass('preview-video')) {
					el.hide();
					
					container.data('youtube-player', new YT.Player(container.get(0), {
						height: '100%',
						width: '100%',
						videoId: el.attr('href').match(/v=([^&]*)/)[1]
					}));
				}

				jQuery(this).colorbox(config);
			});

			GlobalStories.Story.resizePanelLayout();
			
			window.setTimeout(function () {
				jQuery(window).trigger('resize');
			}, 200);
			
			if (window.addthis) {
				console.log('FOUND ADD THIS SO ENABLE THIS THINGS')
				var addthis_share = {templates: {twitter: "Watch this on #globalstories {{url}}."}}
				var addthis_config = { data_track_clickback: false } 
			}
                        
                        // TMB: Add click event for new back to map button
                        // I tried to add this at the same time that to original handler is mapped, but it appears the element does not exist at that point.
                        jQuery('.story-share-header .back-to-map').click(function (event) {
                                event.preventDefault();
                                $("#back-to-story").attr('href',window.location.pathname);
				$("#back-to-story").show();
				History.pushState(null, null, this.href);
                        });
		},
		
		show: function show(url) {
			var container = jQuery('#pages'),
				wrapper = jQuery('#story-pages'),
				stories = wrapper.data('stories') || {};
			
			if (!jQuery(document.body).hasClass('story-view')) {
				container.animate({top: container.height() * -0.5 + 'px'}, 1000);
				jQuery(document.body).removeClass('map-view').addClass('story-view');
			}
				
			if (url in stories) {
				wrapper.find('.current').hide().removeClass('current');
				stories[url].show().addClass('current');
			}
			else {
				wrapper.find('.current').hide().removeClass('current');
				var story = jQuery('<div />').addClass('story current loading').appendTo(wrapper);
				
				stories[url] = story;
				
				wrapper.data('stories', stories);
				
				jQuery.ajax({
					url: url,
					data: {
						ajax: 'true'
					},
					success: function success(data, jqXHR, textStatus) {
						story.html(data);
						GlobalStories.Story.storyLoaded(story);
						story.removeClass('loading');
					}
				});
			}
		},
		
		hide: function hide(event) {
			if (event.preventDefault) {
				event.preventDefault();
			}
			
			var container = jQuery('#pages'),
				wrapper = jQuery('#story-pages');
			
			if (jQuery(document.body).hasClass('story-view')) {
				
				try {
					wrapper.find('.story.current .preview-video').data('youtube-player').pauseVideo();
				}
				catch (e) {
					// Most likely the user is not using the YT player
				}
				
				container.animate({top: 0}, 1000, typeof event === 'function' ? event : function () {});
				jQuery(document.body).removeClass('story-view');
			}
		},
	
		handleTagClick: function handleTagClick(event) {
			var tag = jQuery(this);
			
			GlobalStories.Map.Types.enableAll();
			
			GlobalStories.Map.Filter.setActiveTag(tag.attr('data-story-tag'));

			GlobalStories.Story.hide(function () {
				GlobalStories.Map.Filter.show('#toolbar-filters')
			});
		},
		
		resizePanelLayout: function resizePanelLayout() {
			var panel = jQuery('.story.current .story-media .panel'),
				content = panel.find('.content'),
				footer = panel.find('.footer');

			footer.height(panel.height() - content.outerHeight(true) - 10);
			
			GlobalStories.Story.refreshTwitterWidget();
		},

		initTwitterWidget: function initTwitterWidget(element) {
			jQuery('.twtr-bd .twtr-timeline', element)
				.jScrollPane({
					showArrows: false,
					verticalGutter: 6
				});
		},
		
		refreshTwitterWidget: function refreshTwitterWidget() {
			var container = jQuery('.story.current .story-twitter'),
				header = container.find('.twtr-hd'),
				body = container.find('.twtr-bd'),
				newHeight = container.height() - header.height() - 36,
				JSP = body.find('.twtr-timeline').data('jsp');

			body.height(newHeight);
				
			if (JSP) {
				JSP.reinitialise();
			}
				
		}
	}

})();



jQuery(document).ready(GlobalStories.init);
