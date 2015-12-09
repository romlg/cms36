$(function() {
    // форма комментировани€
    $('.btn_add_comment').fancybox({
        'centerOnScroll':   true,
        'onStart':          function(handler) {
            var prefix = 'comment';
            var pid = $(handler).prop('id').length ? $(handler).prop('id').replace(/tree/gi, '') : '';

            $('#' + prefix + '_form input#pid').val(pid);
            $('#' + prefix + '_form .form_content').show();

            $('#' + prefix + '_form').unbind('submit').bind('submit', function() {
                if ($(this).find('textarea.comment').val().length < 1) {
                    $('#' + prefix + '_form_error').text('¬ведите комментарий').show();
                } else {
                    $.fancybox.showActivity();
                    $(this).trigger('saveUserOpinion', [prefix, $(this).serialize()]);
                }
                return false;
            });

            return true;
        },
        'onClosed':         function(handler) {
            $('#comment_form_error, #comment_form_success').empty().hide();
        }
    });

    $('#comment_form').bind('saveUserOpinion', function(event, form_prefix, data) {
        $.ajax({
            url        : ajax_request_url,
            type       : 'POST',
            data       : data,
            dataType   : 'json',
            success    : function(data) {
                if (data.success === true) {
                    if (data.visible) { // комментарий сразу опубликован, без модерации
                        location.reload();
                    } else {
                        $.fancybox.hideActivity();
                        $('#' + form_prefix + '_form_success').html(data.message).show();
                        $('#' + form_prefix + '_form .form_content').hide();
                        $('#' + form_prefix + '_form_error').empty().hide();
                    }
                } else {
                    $('#' + form_prefix + '_form_error').html(data.message).show();
                }
            },
            error      : function() {
                $.fancybox.hideActivity();
            }
        });
    });

});