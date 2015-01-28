$(document).ready(function() { 
    $('.btn_action').on('click', function() {
        action  = $(this).attr('data-action');
        content = "";
        postdata = undefined;

        //making sure that it's empty before loading anything in there
        $('.patternModal').find('.modal-body').remove();
        $('.patternModal').find('.modal-footer').remove();

        
        if( action == 'edit_pattern' ) {
            postdata =  { "id": $(this).attr('data-id'), "action": action };
        }

        if( action == 'add_pattern' ) {
            postdata = { "action": action };
        }

        if( action == 'delete_pattern') {
            confirmed = confirm("Do you really want to remove given pattern?");

            if( confirmed ) {
                postdata = {'id': $(this).attr('data-id'), 'action' : action };
            } else {
                console.log(postdata);
            }
        }

        if( action == 'activate_pattern' ) {
            keys = [];
            $.each( $('.pattern_activation').find('input[type=checkbox]:checked'), function(k, v) {
                keys.push( $(v).attr('data-value') );
            });

            if( keys.length > 0 ) {
                postdata = { "ids": keys, "action": action };
            }
        }

        // extract modal form in generic container
        if( postdata !== undefined ) {
            $.ajax({
                url: '../modules/addons/domain_blocker/DomainBlockerAjax.php',
                type: "post",
                data: postdata, 
                async: false,
                success: function( res ) {
                    content = res;
                }
            }); 
        }

        //we don't need modal window when deleting pattern instance
        if( !/(delete|activate)/i.test(action) ) {
            $('.patternModal').find('.modal-body').remove();
            $('.patternModal').find('.modal-footer').remove();

            $('.patternModal').append(content);
            $('.patternModal').modal();
            content = ""; 
        } else {
            window.location.reload();
        }  

    });
});
