<#1>
	<?php

	$fields = array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'is_online' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true
		),
		'latitude' => array(
			'type' => 'float',
		),
		'longitude' => array(
			'type' => 'float',
		),
		'elevation' => array(
			'type' => 'float',
		),
		'address' => array(
			'type' => 'text',
			'length' => 256,
			'notnull' => false
		),
		'init_mob_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'comment_mob_id' => array(
			'type' => 'integer',
			'length' => 4,
		),
		'container_id' => array(
			'type' => 'integer',
			'length' => 4,
		)
	);

	$ilDB->createTable("rep_robj_xlel_data", $fields);
	$ilDB->addPrimaryKey("rep_robj_xlel_data", array("id"));


	$fields = array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'ref_id' => array(
			'type' => 'integer',
			'length' => 4,
		),
		'parent_id' => array(
			'type' => 'integer',
			'length' => 4,
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
		),
		'title' => array(
			'type' => 'text',
			'length' => 256,
		),
		'description' => array(
			'type' => 'text',
			'length' => 256,
		),
		'body' => array(
			'type' => 'clob'
		),
		'creation_date' => array(
			'type' => 'timestamp',
		),
		'media_id' => array(
			'type' => 'integer',
			'length' => 4,
		)
	);

	$ilDB->createTable("rep_robj_xlel_comments", $fields);
	$ilDB->addPrimaryKey("rep_robj_xlel_comments", array("id"));
	$ilDB->createSequence("rep_robj_xlel_comments");

	?>

<#2>
		<?php

		include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/class.ilLearnLocPlugin.php');

		$fields = array(
			ilLearnLocPlugin::TYPE . '_key' => array(
				'type' => 'text',
				'length' => 64,
			),
			ilLearnLocPlugin::TYPE . '_value' => array(
				'type' => 'text',
				'length' => 64,
			)
		);

		$ilDB->createTable('rep_robj_'.ilLearnLocPlugin::TYPE.'_conf', $fields);
		$ilDB->addPrimaryKey('rep_robj_'.ilLearnLocPlugin::TYPE.'_conf', array(ilLearnLocPlugin::TYPE . '_key'));
		?>
<#3>
	<?php

	$ilDB->addTableColumn("rep_robj_xlel_data", "export_kw", array("type" => "text", "length" => 1024));

	?>
<#4>
		<?php

		if(!$ilDB->tableExists('rep_robj_xlel_conf')) {
			$fields = array(
				'lel_key' => array(
					'type' => 'text',
					'length' => 64,
				),
				'lel_value' => array(
					'type' => 'text',
					'length' => 64,
				)
			);

			$ilDB->createTable("rep_robj_xlel_conf", $fields);
			$ilDB->addPrimaryKey("rep_robj_xlel_conf", array("lel_key"));
		}

		?>
<#5>
<?php
//$q = "INSERT INTO mob_usage (id, usage_type, usage_id, usage_hist_nr, usage_lang)
//SELECT media_id AS id, 'mep' AS usage_type, ref_id AS usage_id, 0 as usage_hist_nr, '-' AS usage_lang FROM rep_robj_xlel_data AS dat
//INNER JOIN rep_robj_xlel_comments AS com ON dat.id = com.ref_id;";
$q = "INSERT INTO mob_usage (id, usage_type, usage_id, usage_hist_nr)
SELECT media_id AS id, 'mep' AS usage_type, ref_id AS usage_id, 0 as usage_hist_nr FROM rep_robj_xlel_data AS dat
INNER JOIN rep_robj_xlel_comments AS com ON dat.id = com.ref_id
WHERE media_id > 0;";
//$ilDB->manipulate($q);

//$q = "INSERT INTO mob_usage (id, usage_type, usage_id, usage_hist_nr, usage_lang)
//SELECT init_mob_id AS id, 'mep' AS usage_type, id AS usage_id, 0 as usage_hist_nr, '-' AS usage_lang FROM rep_robj_xlel_data WHERE init_mob_id IS NOT NULL;";

$q = "INSERT INTO mob_usage (id, usage_type, usage_id, usage_hist_nr)
SELECT init_mob_id AS id, 'mep' AS usage_type, id AS usage_id, 0 as usage_hist_nr FROM rep_robj_xlel_data
WHERE init_mob_id > 0;";
//$ilDB->manipulate($q);
?>
<#6>
<?php
global $ilDB;
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/Config/class.xlelConfig.php');
xlelConfig::installDB();

$res = $ilDB->query('SELECT * FROM rep_robj_xlel_conf;');
while ($data = $ilDB->fetchObject($res)) {
	$setting[$data->xlel_key] = $data->xlel_value;
}
xlelConfig::set(xlelConfig::F_CAMPUS_TOUR, (int)$setting['campus_tour']);
xlelConfig::set(xlelConfig::F_CAMPUS_TOUR_NODE, (int)$setting['campus_tour_node']);
xlelConfig::set(xlelConfig::F_CAMPUS_TOUR_USERNAME, $setting['campus_tour_username']);
xlelConfig::set(xlelConfig::F_CAMPUS_TOUR_PASSWORD, $setting['campus_tour_password']);
?>
<#7>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc/classes/CheckIn/class.xlelCheckIn.php');
xlelCheckIn::installDB();
?>
<#8>
<?php
$res = $ilDB->query("SELECT * FROM rep_robj_xlel_data");
while ($data = $ilDB->fetchObject($res)) {
	$ilDB->manipulate('DELETE FROM mob_usage WHERE id = ' . $ilDB->quote($data->init_mob_id, 'integer'));
	//			$r = $ilDB->query('SELECT COUNT(*) AS cnt FROM mob_usage WHERE id = ' . $ilDB->quote($data->init_mob_id, 'integer'));
	//			$has_set = $ilDB->fetchObject($r);
	//			if ((int)$has_set->cnt < 1) {
	if(!$data->init_mob_id OR !$data->id) {
		continue;
	}

	$ilDB->insert('mob_usage', array(
		'id'            => array( 'integer', $data->init_mob_id ),
		'usage_type'    => array( 'text', 'mep' ),
		'usage_id'      => array( 'integer', $data->id ),
		'usage_hist_nr' => array( 'integer', 0 ),
	));
	//			}
}

$res = $ilDB->query("SELECT * FROM rep_robj_xlel_comments");
while ($data = $ilDB->fetchObject($res)) {
	$ilDB->manipulate('DELETE FROM mob_usage WHERE id = ' . $ilDB->quote($data->media_id, 'integer'));
	//			$r = $ilDB->query('SELECT COUNT(*) AS cnt FROM mob_usage WHERE id = ' . $ilDB->quote($data->media_id, 'integer'));
	//			$has_set = $ilDB->fetchObject($r);
	//			if ((int)$has_set->cnt < 1) {

	if (!$data->media_id OR !$data->ref_id) {
		continue;
	}

	$ilDB->insert('mob_usage', array(
		'id'            => array( 'integer', $data->media_id ),
		'usage_type'    => array( 'text', 'mep' ),
		'usage_id'      => array( 'integer', $data->ref_id ),
		'usage_hist_nr' => array( 'integer', 0 ),
	));
	//			}
}
?>