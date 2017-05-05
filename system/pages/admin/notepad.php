<?php
/**
 * Notepad
 *
 * @package   MyAAC
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.0.5
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Notepad';

$notepad_content = Notepad::get($account_logged->getId());
if(isset($_POST['content']))
{
	$_content = html_entity_decode(stripslashes($_POST['content']));
	if(!$notepad_content)
		Notepad::create($account_logged->getId(), $_content);
	else
		Notepad::update($account_logged->getId(), $_content);

	echo '<div class="success" style="text-align: center;">Saved at ' . date('g:i A') . '</div>';
}
else
{
	if($notepad_content !== false)
		$_content = $notepad_content;
}
?>

<table width="700" cellspacing="1" cellpadding="2" border="0" align="center">
	<form method="post">
		<tr>
			<td align="center">
				<p>This is your personal notepad. Be sure to save it each time you modify something.</p>
			</td>
		</tr>
		<tr>
			<td align="center">
				<textarea style="text-align: left;" name="content" cols="50" rows="15" onchange="notepad_onchange(this);"><?php echo isset($_content) ? htmlentities($_content, ENT_COMPAT, 'UTF-8') : ''; ?></textarea>
			</td>
		</tr>
		<tr>
			<td align="center">
				<input type="submit" name="submit" onclick="notepad_save(this);" value="Save" />
			</td>
		</tr>
	</form>
</table>

<?php
	// confirm leaving current page if content of the notepad has been modified
?>
<script type="text/javascript">
var original_value = document.getElementsByName("content")[0].value;

function confirm_exit(e) {
	var e = e || window.event;
	var message = 'Are you sure you want to quit? Remaining changes will be unsaved.';

	// for IE and Firefox prior to version 4
	if (e) {
		e.returnValue = message;
	}

	// for Safari
	return message;
};

function notepad_onchange(e) {
	if(original_value != e.value) {
		window.onbeforeunload = confirm_exit;
	}
	return true;
};

function notepad_save(e) {
	window.onbeforeunload = function(e) {};
	return true;
};
</script>

<?php
class Notepad
{
	static public function get($account_id)
	{
		global $db;
		$query = $db->select(TABLE_PREFIX . 'notepad', array('account_id' => $account_id));
		if($query !== false)
			return $query['content'];

		return false;
	}

	static public function create($account_id, $content = '')
	{
		global $db;
		$db->insert(TABLE_PREFIX . 'notepad', array('account_id' => $account_id, 'content' => $content));
	}

	static public function update($account_id, $content = '')
	{
		global $db;
		$db->update(TABLE_PREFIX . 'notepad', array('content' => $content), array('account_id' => $account_id));
	}
}
