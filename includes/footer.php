<?php
// footer.php
?>
    </div></div><script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/AdminLTE.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
    // Activate tab based on URL parameter
    var urlParams = new URLSearchParams(window.location.search);
    var activeTab = urlParams.get('tab') || 'category';
    
    // Show the active tab
    $('.nav-tabs a[href="#' + activeTab + '"]').tab('show');
    
    // Update URL when tab is clicked
    $('.nav-tabs a').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).attr('href').substring(1);
        window.history.replaceState(null, null, '?tab=' + tab);
        $(this).tab('show');
    });
});
</script>
</body>
</html>