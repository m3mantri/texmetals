/*
    Author  : I.CUBE, inc.
    main jQuery widget of Rodalink/default
*/

define([
  'jquery',
  'jquery/ui',
  'covervid',
  'lightslider',
  'bxslider',
  'sss',
  'fancybox',
  'matchMedia'
], function($, ui){
    'use strict';

    $.widget('texmetals.texmetals', {

        _create: function() {
            this.initAllPages();
            this.initHomePage();
            this.initCategoryPage();
            this.initProductPage();
            this.initSearchPage();
            this.initCategorySearchPage();
            this.initShoppingCartPage();
            this.initCheckoutPage();
            this.initRegisterPage();
            this.initCustomerAccountPage();
            this.initInvoicePage();
            this.initCmsPage();
            this.initContactPage();
            this.initLimitCheckout();
        },

        initAllPages: function() {

        // Sticky Header
        $(window).scroll(function() {
            if ($(this).scrollTop() > 1){  
                $('.page-header').addClass("sticky");
            }
            else{
                $('.page-header').removeClass("sticky");
            }
        });

        $(".slider").sss();

        $("#featured-in").lightSlider({
            item: 6,
            auto: true,
            loop: true,
            responsive: [
            {
                breakpoint:881,
                settings: {
                    item:5,
                }
            },
            {
                breakpoint:700,
                settings: {
                    item:4,
                }
            },
            {
                breakpoint:519,
                settings: {
                    item:3,
                }
            },
            {
                breakpoint: 338,
                settings: {
                    item: 2,
                }
            },
            ]
        });
        $('.lslide').on('mouseenter', function () {
            $(".lslide:not(:hover)").fadeTo( 200, 0.5 );;
        });

        $('.lslide').on('mouseleave', function () {
            $(".lslide:not(:hover)").fadeTo( 0, 1 );;
        });
        $(".iframe").fancybox();
        $( "#home-projects ul li a" ).hover(
            function() {
                $("#home-projects ul").removeClass();
                var myclass=$(this).attr('class');
                $("#home-projects ul").addClass(myclass+"_1");
            //alert(myclass);
        }, function() {
            //  $( this ).removeClass( "hover" );
        }
        );
        $("#selltous").lightSlider({
            item: 1,
            auto: true,
            loop: true,
            speed: 0,
            controls: false,
            slideMargin: 0,
            pause: 8000,
            enableTouch: false,
            enableDrag: false,
        });

        $("#dealerpage").lightSlider({
            item: 1,
            auto: true,
            loop: true,
            speed: 0,
            controls: false,
            slideMargin: 0,
            pause: 8000,
            enableTouch: false,
            enableDrag: false,
        });

        // Footer
        $(".collapsed-block h3").click(function(){
        $(this).parent().children('.tabBlock').slideToggle();
        $(this).toggleClass("active");
        });
        $(".block.newsletter h3").click(function(){
        $(this).parent().parent().children('.content').slideToggle();
        $(this).toggleClass("active");
        });      
        },

        initHomePage: function() {
            
            if ($('body.cms-index-index').length) {

                // Featured Products Custom
                $('.featured-wrapper .product-items').bxSlider({
                    pagerCustom: '#bx-pager'
                }); 

                // Blog Tab
                $(".news-item").hide();
                $(".news-flash li:first-child").addClass('active-tab');
                var defaultActive = $(".news-flash li.active-tab a").attr("href");
                $(defaultActive).show();

                $(".news-flash li a").click(function (e) {
                  e.preventDefault();
                  $(".news-flash li").removeClass("active-tab");
                  var related = $(this).attr("href"); //store href value
                  $(this).parent().addClass("active-tab");
                  if ($(this).parent().hasClass("active-tab")) {
                    $(".news-item").hide();
                    $(related).show();
                  }
                })

                // Slider Featured In, Mobile
                var media_check = window.matchMedia('only screen and (max-width: 800px)');
                if (media_check.matches) {
                    $("#featured-market").lightSlider({
                        item: 1,
                        controls: true,
                        slideMargin: 0,
                        pause: 8000,
                        enableTouch: false,
                        enableDrag: false,
                        pager: false,
                        responsive : [
                            {
                                breakpoint:800,
                                settings: {
                                    item:3,
                                  }
                            },
                            {
                                breakpoint:767,
                                settings: {
                                    item:1,
                                  }
                            }
                        ]
                    });   
                }

            }
        },

        initCategoryPage: function() {

            if ($('body.catalog-category-view').length) {
                // Featured Products Custom
                $('.featured-wrapper .product-items').bxSlider({
                    pagerCustom: '#bx-pager'
                });
            }
        },

        initProductPage: function() {

            if ($('body.catalog-product-view').length) {
              // Related Products
                $('.block.related .product-items').bxSlider({
                    pagerCustom: '#bx-pager'
                });

                // Upsell Product
                $('.block.upsell .product-items').bxSlider({
                });
            }
        },

        initSearchPage: function() {

            if ($('body.catalogsearch-result-index').length) {
            }
        },

        initCategorySearchPage: function() {

            if ($('body.catalog-category-view').length || $('body.catalogsearch-result-index').length) {
            }
        },

        initShoppingCartPage: function() {

            if ($('body.checkout-cart-index').length) {
            $('input').click(function () {
            //check if checkbox is checked
            if ($(this).is(':checked')) {

                $('.disclaimer_popup .popup').removeAttr('disabled'); //enable input

            } else {
                $('.disclaimer_popup .popup').attr('disabled', true); //disable input
            }
            });
            
            $('button.popup').click(function(){
                var href = $(this).attr('href');
                var text = $(this).text();
                $(this).attr('onclick','location.href="'+href+'";');
                $(this).attr('disabled', true);
                $(this).clone().appendTo('.disclaimer_popup');
                $('.mike-container').show();
                $('.disclaimer_popup h2').text(text);
                $('.disclaimer_popup .check input').attr('checked', false);
            });

            $('.b-close').after().click(function(){
                $('.mike-container').hide();
                $('.disclaimer_popup button.popup').remove();
                $('.disclaimer_popup .check').attr('checked', false);
                $('button.popup').attr('disabled', false);
            });

            $('.qtyplus').click(function(){
                setTimeout(function() {
                   $('.action.update').click();
               }, 10);
            });
            $('.qtyminus').click(function(){
                setTimeout(function() {
                   $('.action.update').click();
               }, 10);
            });

            $('.control.qty .qtyplus').click(function(e){
            // Stop acting like a button
            e.preventDefault();
            // Get the field name
            // var fieldName = $(this).attr('field');
            // Get its current value
            var currentVal = parseInt($(this).parent().children('.qty').val());
            // If is not undefined
            if (!isNaN(currentVal)) {
                // Increment
                $(this).parent().children('.qty').val(currentVal + 1);
            } else {
                // Otherwise put a 0 there
                $(this).parent().children('.qty').val(0);
            }
            });
            // This button will decrement the value till 0
            $(".control.qty .qtyminus").click(function(e) {
                // Stop acting like a button
                e.preventDefault();
                // Get the field name
                // var fieldName = $(this).attr('field');
                // Get its current value
                var currentVal = parseInt($(this).parent().children('.qty').val());
                // If it isn't undefined or its greater than 0
                if (!isNaN(currentVal) && currentVal > 0) {
                    // Decrement one
                    $(this).parent().children('.qty').val(currentVal - 1);
                } else {
                    // Otherwise put a 0 there
                    $(this).parent().children('.qty').val(0);
                }
            });  
            }
        },

        initCheckoutPage: function() {

            if ($('body.checkout-onepage-index').length) {  
            }
        },

        initRegisterPage: function() {

            if ($('body.customer-account-create').length) {
            }
        },

        initCustomerAccountPage: function() {
            
            if( $('body.account').length ) {
            }
        },
        
        initInvoicePage: function() {
            if ($('body.sales-order-invoice').length) {
            }
        },

        initCmsPage: function() {

            if ($('body.cms-page-view').length) {   

                // Smooth Scrolling
                $("#vaultarrow").click(function() {
                    $('html, body').animate({
                        scrollTop: $("#linktosection2").offset().top
                    }, 2000);
                });

                // Accordion Cayman Page
                var allPanels = $('.accordion > dd').hide();
                var removeClass = $('.accordion > dt > span');
                $('.accordion > dd:first').show();
                $('.accordion > dt:first > span').addClass('shown');

                $('.accordion > dt > span').click(function() {
                    $(this).parent().next().slideToggle();
                    $(this).parent().toggleClass('title');
                    $(this).toggleClass('shown');
                    return false;
                });

                // Get Selected Buyers Guide Step
                var pathname = window.location.pathname.substring(1);
                if (pathname == 'buyers-guide') {
                    $('.cms-buyers-guide .widgt-box-buyer-guide .nav-container li:nth-child(1)').addClass('selected');
                } else if (pathname == 'the-decision-process') {
                    $('.cms-buyers-guide .widgt-box-buyer-guide .nav-container li:nth-child(2)').addClass('selected');
                } else if (pathname == 'common-questions') {
                    $('.cms-buyers-guide .widgt-box-buyer-guide .nav-container li:nth-child(3)').addClass('selected');
                } else if (pathname == 'buying-selling-process') {
                    $('.cms-buyers-guide .widgt-box-buyer-guide .nav-container li:nth-child(4)').addClass('selected');
                } else if (pathname == 'buying-selling-process-ira-rollovers') {
                    $('.cms-buyers-guide .widgt-box-buyer-guide .nav-container li:last-child').addClass('selected');
                }

                // Get video height and width
                var masthead = $('.masthead');
                var windowH = $(window).height();

                masthead.height(windowH);

                var media_check = window.matchMedia('only screen and (min-width: 1900px)');
                if (media_check.matches) {
                    // Get video height and width
                    var masthead = $('.masthead');
                    var windowH = $(window).height();
                    var windowW = $(window).width();

                masthead.width(windowW);
                masthead.height(windowH);

                    $('.masthead-video').coverVid(1920, 1080);
                }

                var mobile_check = window.matchMedia('only screen and (max-width: 767px)');
                if (mobile_check.matches) {
                    var mastheadmobile = $('.masthead-mobile');
                    var windowmobile = $(window).height();
                    mastheadmobile.height(windowmobile);
                    
                    $('.page-main .masthead').removeClass('masthead');
                }

            }
        },
        initContactPage: function() {
            if ($('body.contact-index-index').length) {   
            }
        },
        initLimitCheckout: function() {
             
            $('.limit-step .form-content').hide();

            if ($('body.limitorders-checkout-index').length 
                || $('body.sellback-limitorder-index').length) {
                /* show first step only */
                var firstStep = 'limitorder';
                $('.limit-step.' + firstStep + ' .form-content').show();
            }

            if ($('body.sellback-checkout-index').length) {
                /* show first step only */
                var firstStep = 'metal-condition';
                $('.limit-step.' + firstStep + ' .form-content').show();
            }

            /* Edit Step */
            // $('.limit-step .step-edit').click(function(){
            $(document).on('click', '.limit-step .step-edit', function() {
                /* remove edit label */
                $(this).children('.edit-label').detach();
                $(this).removeClass("step-edit");
                $(this).parent().nextAll().children('legend').children('.edit-label').detach();
                $(this).parent().nextAll().children("legend").removeClass("step-edit");
                /* show the step & hide others steps */
                $(this).next().next().show();
                $(this).parent().siblings().children('.form-content').hide();
            });

        }
    });

    return $.texmetals.main;

});