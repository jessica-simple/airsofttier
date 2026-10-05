(function($) {
    'use strict';

    function safeDisplayRevisions(rev, $list) {
        $list.empty();

        var revisions = rev;

        if (typeof revisions === 'string') {
            try {
                revisions = JSON.parse(revisions);
            } catch (e) {
                revisions = {};
            }
        }

        if (typeof revisions !== 'object' || revisions === null) {
            revisions = {};
        }

        $.each(revisions, function(i, item) {
            $list.append('<li data-time="' + i + '"><span class="revision-icon mfn-icon-clock"></span><div class="revision"><h6>' + item + '</h6><a class="mfn-option-btn mfn-option-text mfn-option-blue mfn-btn-restore revision-restore" href="#"><span class="text">Restore</span></a></div></li>');
        });

        $('.revision-restore').on('click', function(e) {
            e.preventDefault();
            restoreRev($(this));
        });
    }

    function patchDisplayRevisions() {
        if (typeof displayRevisions !== 'function') {
            return;
        }

        displayRevisions = safeDisplayRevisions;
    }

    $(document).ready(function() {
        patchDisplayRevisions();
    });
})(jQuery);
