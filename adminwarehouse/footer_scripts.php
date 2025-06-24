<?php
// footer_scripts.php
?>
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/AdminLTE.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Live clock
    function updateClock() {
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();
        
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        
        document.getElementById('live-clock').textContent = hours + ':' + minutes + ':' + seconds;
        setTimeout(updateClock, 1000);
    }
    updateClock();
    
    // Notifications dropdown
    $(document).ready(function() {
        $('.notifications-menu .dropdown-toggle').click(function(e) {
            e.preventDefault();
            $(this).parent().toggleClass('open');
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('.notifications-menu').length) {
                $('.notifications-menu').removeClass('open');
            }
        });
        
        // Small box hover effects
        $('.small-box').hover(
            function() {
                $(this).find('.icon').css('font-size', '80px');
            },
            function() {
                $(this).find('.icon').css('font-size', '70px');
            }
        );
    });
</script>