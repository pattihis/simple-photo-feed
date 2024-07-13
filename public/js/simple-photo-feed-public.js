window.onload = function () {
  (function ($) {
    let spfJS;

    class SpfJS {
      constructor() {
        let images;
        let index;
        let bodyOverflow;
        let htmlOverflow;

        console.log('SpfJS Constructor');
        $(document).on('click', '.spf_lightbox', this.lightboxInit);
        $(document).on('click', '#spf_lightbox_close', this.lightboxClose);
        $(document).on('click', '#spf_lightbox_next', this.lightboxNext);
        $(document).on('click', '#spf_lightbox_prev', this.lightboxPrev);

        $(document).keydown(function (e) {
          if (e.keyCode == 39) {
            // Right Arrow
            spfJS.lightboxNext(e);
          }
          if (e.keyCode == 37) {
            // Left Arrow
            spfJS.lightboxPrev(e);
          }
          if (e.keyCode == 27) {
            // Escape
            spfJS.lightboxClose(e);
          }
        });

        spfJS = this;
      }

      lightboxNext(e) {
        e.preventDefault();
        if (spfJS.index < spfJS.images.length - 1) {
          spfJS.index++;
        } else {
          spfJS.index = 0;
        }

        spfJS.setImage(spfJS.index);
      }

      lightboxPrev(e) {
        e.preventDefault();
        if (spfJS.index > 0) {
          spfJS.index--;
        } else {
          spfJS.index = spfJS.images.length - 1;
        }

        spfJS.setImage(spfJS.index);
      }

      lightboxClose(e) {
        e.preventDefault();
        $('#spf_lightbox_container').fadeOut(500, function () {
          $(this).remove();
        });

        $('body').css('overflow', spfJS.bodyOverflow);
        $('html').css('overflow', spfJS.htmlOverflow);
      }

      renderLightbox() {
        spfJS.bodyOverflow = $('body').css('overflow');
        spfJS.htmlOverflow = $('html').css('overflow');

        let img = document.createElement('img');

        let imageBox = document.createElement('div');
        $(imageBox).attr('id', 'spf_lightbox_image_box').append(img);

        let nextButton = document.createElement('a');
        $(nextButton)
          .attr('id', 'spf_lightbox_next')
          .attr('href', '#')
          .attr('title', 'Next image')
          .html(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="88" y1="128" x2="168" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><polyline points="136 96 168 128 136 160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>'
          );

        let prevButton = document.createElement('a');
        $(prevButton)
          .attr('id', 'spf_lightbox_prev')
          .attr('href', '#')
          .attr('title', 'Previous image')
          .html(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="88" y1="128" x2="168" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><polyline points="120 96 88 128 120 160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>'
          );

        let closeButton = document.createElement('a');
        $(closeButton)
          .attr('id', 'spf_lightbox_close')
          .attr('href', '#')
          .attr('title', 'Close lightbox')
          .html(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><line x1="160" y1="96" x2="96" y2="160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="96" y1="96" x2="160" y2="160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>'
          );

        let caption = document.createElement('div');
        $(caption).attr('id', 'spf_lightbox_caption');

        let link = document.createElement('a');
        $(link)
          .attr('href', '#')
          .attr('id', 'spf_lightbox_link')
          .html('View on Instagram')
          .attr('target', '_blank');

        let container = document.createElement('div');
        $(container)
          .attr('id', 'spf_lightbox_container')
          .append(imageBox)
          .append(nextButton)
          .append(prevButton)
          .append(closeButton)
          .append(caption)
          .append(link)
          .css('display', 'none');

        $('body').append(container);

        $('#spf_lightbox_container').fadeIn();

        $('body').css('overflow', 'hidden');
        $('html').css('overflow', 'hidden');
      }

      lightboxInit(e) {
        e.preventDefault();

        spfJS.images = document.querySelectorAll('.spf_lightbox');
        spfJS.index = $(this).data('i');

        spfJS.renderLightbox();

        spfJS.setImage(spfJS.index);
      }

      setImage(index) {
        let image = spfJS.images[index];
        console.log(image);
        $('#spf_lightbox_image_box img').fadeOut(250, function () {
          $('#spf_lightbox_image_box img').attr('src', $(image).data('src')).fadeIn(250);
        });
        $('#spf_lightbox_caption').html($(image).attr('title'));
        $('#spf_lightbox_link').attr('href', $(image).data('url'));
      }
    }

    new SpfJS();
  })(jQuery);
};
