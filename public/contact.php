<?php
// part of orsee. see orsee.org
ob_start();
$menu__area="contact";
$title="contact";
include ("header.php");
if ($proceed) {
	if ($settings['show_public_contact']!='y') redirect("public/");
}
if ($proceed) {
	echo '<center>
			<BR>
			<TABLE class="or_formtable" style="width: 80%"><TR><TD>';
		echo content__get_content("contact");
		echo '
			</TD></TR></TABLE>

			</center>';

}
include ("footer.php");
?>