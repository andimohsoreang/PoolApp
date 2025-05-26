/*=================================

Template name: Mobikit
Version: 1.0
Author: SITLBD       
Author url: https://www.sitlbd.com/  
Developer: Najmul Huda Eimon 

===================================*/


  $(function(){
  /*================================
    01. Fixed menubar
    =================================*/
    $navOffset = $('.dz-menubar').offset().top;
    $(window).scroll(function(){
        $scroll = $(this).scrollTop();

        if($scroll > $navOffset){
            $('.dz-menubar').addClass('fixed');
        }else{
            $('.dz-menubar').removeClass('fixed');
        }
    });

    /*================================
    03. Scroll to top button
    =================================*/
    $('.top-btn').on('click',function(){
        $('html').animate({
            scrollTop: 0
        },1000);
    });

    $(window).on('scroll',function(){
        var $scroll = $(this).scrollTop();

        if($scroll > 300){
            $('.top-btn').addClass('show');
        }else{
            $('.top-btn').removeClass('show');
        }
    });

    /*=====================================================================
        05: Smooth scroll
    ======================================================================*/
    $(".scroll-down").on("click", function (t) {
        t.preventDefault();
    var i = this.hash;
        $("html,body").animate({ scrollTop: $(i).offset().top }, 700);
    });

    /*================================================================
      01. grid
    =================================================================*/

    let vline = document.querySelectorAll('.dz-vline');
        vline.forEach(function(cur){
            cur.style.flex = `0 0 calc(calc( ${cur.getAttribute('data-display')} / 12 ) * 100%)`;
            cur.style.width = `calc(calc( ${cur.getAttribute('data-display')} / 12 ) * 100%)`;
        });

    /*================================================================
      01. background image
    =================================================================*/
    document.querySelectorAll('[data-img]').forEach(function(cur){
        cur.style.backgroundImage = 'url(' + cur.getAttribute('data-img') + ')';
        cur.style.backgroundSize = 'cover';
        cur.style.backgroundRepeat = 'no-repeat';
        cur.style.backgroundPosition = 'center';
    });

    /*================================================================
      01. go back function
    =================================================================*/
    let goBack = document.querySelector('.goBack');
       let goBackPage = ()=>{history.go(-1)}
       goBack ? goBack.addEventListener('click', goBackPage) : null

    /*================================================================
      01. svg image change
    =================================================================*/

    const convertImages = (query, callback) => {
        const images = document.querySelectorAll(query);
        
            images.forEach(image => {
            fetch(image.src)
            .then(res => res.text())
            .then(data => {
                const parser = new DOMParser();
                const svg = parser.parseFromString(data, 'image/svg+xml').querySelector('svg');
        
                if (image.id) svg.id = image.id;
                if (image.className) svg.classList = image.classList;
        
                image.parentNode.replaceChild(svg, image);
            })
            .then(callback)
            .catch(error =>error);
            });
        };
        convertImages('.svg');

    /*================================================================
      01. swiper slider
    =================================================================*/

        // slider 1
      var swiper = new Swiper('.dz-near',{
        slidesPerView: 2,
        spaceBetween: 10,
        observer: true,
      observeParents: true,
      loop: true,
      centeredSlides: false,
      breakpoints: {
          1920: {
            slidesPerView: 2,
            initialSlide: 1,
          },
          1450: {
            slidesPerView: 2,
            initialSlide: 1,
          },
          767: {
              slidesPerView: 2,
          },
          576: {
              slidesPerView: 2,
          },
          320: {
              slidesPerView: 2,
          }
      }
      
    });

    // slider 2
    var swiper = new Swiper('.dz-popular',{
        slidesPerView: 3,
        spaceBetween: 10,
        observer: true,
      observeParents: true,
      loop: true,
      centeredSlides: false,
      breakpoints: {
          1920: {
            slidesPerView: 3,
            initialSlide: 1,
          },
          1450: {
            slidesPerView: 3,
            initialSlide: 1,
          },
          767: {
              slidesPerView: 3,
          },
          576: {
              slidesPerView: 3,
          },
          320: {
              slidesPerView: 3,
          }
      }
      
    });
    

});