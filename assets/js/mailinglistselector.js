function attachSelectedLists() {
    var checkedIds = $('.control-list input[type=checkbox]:checked').map(function() {
        return $(this).val();
    }).get();

    if (checkedIds.length === 0) {
        $.oc.flashMsg({text: 'Please select at least one mailing list', 'class': 'warning'});
        return;
    }

    $.request('onAttachLists', {
        data: { checked: checkedIds },
        loading: $.oc.stripeLoadIndicator,
        complete: function() {
            $.oc.flashMsg({text: 'Lists attached!', 'class': 'success'});
            $('.modal-header button.close').trigger('click');
        }
    });
}