jQuery(document).on("click","[data-fs-action='modal']",function(e){e.preventDefault();var t=jQuery(this).attr("href");jQuery(t).fadeIn()}),jQuery(document).on("click","[data-fs-action='modal-close']",function(e){e.preventDefault();var t=jQuery(this).parents(".fs-modal");jQuery(t).fadeOut()});