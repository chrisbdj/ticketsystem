<?php  

session_start();
if(session_destroy()) {
    echo "log out successful";
    header( "Refresh:1; url='index.html'");
    echo "<meta http-equiv='refresh' content=1;URL=\'index.html'>";
}
?>