<?php
/**
 * Export the members of a group to CSV
 */

$group_guid = (int) get_input("group_guid");

if (empty($group_guid)) {
	register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	forward(REFERER);
}

$group = get_entity($group_guid);
if (empty($group) || !elgg_instanceof($group, "group")) {
	register_error(elgg_echo("InvalidParameterException:GUIDNotFound", array($group_guid)));
	forward(REFERER);
}

if (!$group->canEdit() || (elgg_get_plugin_setting("member_export", "group_tools") != "yes")) {
	register_error(elgg_echo("groups:cantedit"));
	forward(REFERER);
}

// create temp file
$fh = tmpfile();

$headers = array(
	"name",
	"username",
	"email",
	"member since (unix)",
	"member since (YYYY-MM-DD HH:MM:SS)",
);
$profile_fields = elgg_get_config("profile_fields");
if (!empty($profile_fields)) {
	foreach ($profile_fields as $metadata_name => $type) {
		$lan_key = "profile:" . $metadata_name;
		$header = elgg_echo($lan_key);
		if ($header == $lan_key) {
			$header = $metadata_name;
		}
		
		$headers[] = $header;
	}
}
fwrite($fh, "\"" . implode("\";\"", $headers) . "\"" . PHP_EOL);


$options = array(
	"type" => "user",
	"limit" => false,
	"relationship" => "member",
	"relationship_guid" => $group->getGUID(),
	"inverse_relationship" => true
);

$members = new ElggBatch("elgg_get_entities_from_relationship", $options);
foreach ($members as $member) {
	$info = array(
		$member->name,
		$member->username,
		$member->email
	);
	
	$member_since = group_tools_get_membership_information($member, $group);
	$info[] = $member_since;
	$info[] = date("Y-m-d G:i:s", $member_since);
	
	if (!empty($profile_fields)) {
		foreach ($profile_fields as $metadata_name => $type) {
			if ($type == "tags") {
				$info[] = implode(", ", $member->$metadata_name);
			} else {
				$info[] = $member->$metadata_name;
			}
		}
	}
	
	fwrite($fh, "\"" . implode("\";\"", $info) . "\"" . PHP_EOL);
}

// read the csv in to a var before output
$contents = "";
rewind($fh);
while (!feof($fh)) {
	$contents .= fread($fh, 2048);
}

// cleanup the temp file
fclose($fh);

// output the csv
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=\"" . elgg_get_friendly_title($group->name) . ".csv\"");
header("Content-Length: " . strlen($contents));

echo $contents;
exit();
