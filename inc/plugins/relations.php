<?php

/**
 * Relations  - by risuena
 * Beziehungen von Charakteren zueinander
 *  Anfragen im Profil des Charakters
 *    Eigene Kategorien möglich
 *  Default: Familie,Freunde,Liebe,Bekannte,Ungemocht,Sonstiges -> können im ACP geändert werden
 *    Bestätigung nötig
 *    Verwaltung im UCP
 *    Eintragen von NPCs mit Bild auf Wunsch
 *
 * Kontakt: https://lslv.de/risu
 */

// enable for Debugging:
//error_reporting(-1);
//ini_set('display_errors', true);

global $db, $mybb;
// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function relations_info()
{
    return array(
        "name" => "Relations",
        "description" => "Beziehungsplugin mit eigenen Kategorien von Risuena",
        "website" => "https://lslv.de/risu/",
        "author" => "risuena",
        "authorsite" => "https://lslv.de/risu/",
        "version" => "1.1",
        "compatibility" => "18*"
    );
}

function relations_uninstall()
{
    global $db;
    if ($db->field_exists("r_id", "relas")) {
        $db->drop_table("relas");
    }
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "users DROP rela_cat");
    $db->delete_query("templates", "title LIKE 'relas_%'");

    // Einstellungen entfernen
    $db->delete_query('settings', "name LIKE 'relas_%')");
    $db->delete_query('settinggroups', "name = 'relations'");
    rebuild_settings();
}

function relations_install()
{
    global $db;
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "relas` (
	`r_id` int(10) NOT NULL AUTO_INCREMENT,
	`r_from` int(10) NOT NULL DEFAULT 0,
	`r_to` int(10) NOT NULL DEFAULT 0,
	`r_kategorie` varchar(150) NOT NULL DEFAULT '',
	`r_kommentar` varchar(2555) NOT NULL DEFAULT '',
	`r_sort` int(10) NOT NULL DEFAULT 0,
	`r_accepted` int(10) NOT NULL DEFAULT 0,
	`r_npc` int(11) NOT NULL DEfAULT 0,
	`r_npcname` varchar(150) NOT NULL DEFAULT '',
	`r_npcimg` varchar(250) NOT NULL DEFAULT '', 
	PRIMARY KEY (`r_id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    $db->add_column("users", "rela_cat", "varchar(500) NOT NULL default ',Familie,Freunde,Liebe,Bekannte,Ungemocht,Sonstiges,'");
    $template[0] = array(
        "title" => 'relas_accepted',
        "template" => '
				{$titel}
                <form action="" method="post"> 
                    <div class="divbox_ucp_relas">
                    <table>
                        <tr>
                            <td colspan="2"><headtitle> {$who_link} </headtitle>in {$kategorie} mit der Sortierung {$sort} </td>
                        </tr>	
                        <tr>
                            <td colspan="2" align="left"><b>Daten ändern:</b></td>
                        </tr>
                        <tr>
                            {$feld_npcname}
							{$feld_npcimg}
                            <td align="center" valign="top" width="1"><input type="text" name="sort" value="{$sort}" class="rela sort"/></td>
                            <td align="left">
                            <textarea name="kommentar" class="textarea_kommentar">{$kommentar}</textarea>
                            <input type="hidden" name="getrela" value="{$r_id}">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="left"><span class="smalltext">
                            {$inputs_own}
								    

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="hidden" name="getto" value="{$who_id}" />
                                <input type="hidden" name="getfrom" value="{$r_from}" />
                                
                            <input type="submit" name="change" value="ändern/hinzufügen" id="rela_button"/> <input type="submit" name="delete" value="löschen" id="rela_button"/>
                            </td>
                        </tr>
                    </table>
                    </div>
                </form><br /><br />
		',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[1] = array(
        "title" => 'relas_anfragen',
        "template" => '
										<input type="hidden" name="getrela" value="{$r_id}" />
                    <input type="hidden" name="getfrom" value="{$r_fromid}" />
                    <input type="hidden" name="getto" value="{$r_to}" />		
                    {$r_from} hat für die Kategorie <b>{$gefragte_kategorie}</b> angefragt. <br />
                        <div class="kommentarRequest">
                        {$kommentar}
                        </div>
                
                        Vergiss nicht die Anfrage von {$r_from} zu bestätigen: <br /><br />
                        <table>
                            <tr>
                                <td align="center" valign="top" width="1">
                                    Sortierung: <br />
                                <input type="text" name="sort" id="rela_button" />
                                </td>
                                <td align="left">
                                Kommentar:<br />
                            <textarea name="kommentar" id="rela_button"></textarea>
                                </td>
                            </tr>
                        <tr>
                        <td colspan="2" align="left"><span class="smalltext">
                            {$inputsRequest_own}
                            </span>
                        </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="left">
                            <input type="submit" name="reeintragen" value="bestätigen & eintragen" id="rela_button" />
                            <input type="submit" name="ablehnen" value="ablehnen" id="rela_button"/>
                            </td>
                        </tr>
                    </table>
		',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[2] = array(
        "title" => 'relas_memberprofil',
        "template" => '
				<style type="text/css">
				/*wichtig für die richtige Darstellung und Abstände!
				*/
				headtitle_big:first-child {
					padding-top: 0px;
				}
				headtitle_big {
					padding-top: 20px;
					clear: both;
				}
				</style>
				<relabox>
				<form action="" method="post">
						<table width="100%"><tbody>
						<tr>
							<td class="rela_title" colspan="2"><h1>Relations</h1></td>
						</tr>
						<td colspan="2" valign="top" align="left">
							{$showrelas}		
						</td>
					</tr>
					{$rela_anfrage}
					</table>
				
				</form>
				</relabox>',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[3] = array(
        "title" => 'relas_memberprofil_anfrage',
        "template" => '<tr>
	<td class="mem_name" colspan="2">
		<headtitle_big>Anfrage stellen</headtitle_big>
	</td>
</tr>
<tr>
	<td colspan="2" align="center">
		<form action="" method="post">
			<div class="divbox_ucp_relas">
				<table width="80%">
					<tr>
						<td align="right" width="20%">
							<b>Sortierung</b>
						</td>
						<td align="left"><b>Kommentar:</b></td>
					</tr>
					<tr>
						<td align="right" valign="top">
							<input type="number" name="sort" class="rela sort"/><br/>
							<span class="smalltext">1 heißt der Charakter wird als erstes aufgelistet.</span>
						</td>
						<td align="left" valign="top">
							<textarea name="kommentar" class="textarea_kommentar"></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<span class="smalltext">{$inputs_own}</span>
						</td> 
					</tr>
					<tr>
						 <td colspan="2" align="center">	
							<input type="submit" name="anfragen" value="anfragen" id="rela_button"/>
						</td>
					</tr>
				</table>
			</div>
		</form><br /><br />
	</td>
</tr>
		',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[4] = array(
        "title" => 'relas_ucp_offene_nr',
        "template" => '
				<form action="" method="post">
					<div class="divbox_ucp_relas">
						{$reaktion}
					</div>
				<br />
				</form>
    	',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[5] = array(
        "title" => 'relas_usercp',
        "template" => '
					<html>
					<head>
					<title>Relationsverwaltung</title>
					{$headerinclude}
					</head>
					<body>
					{$header}
					<table width="100%" border="0" align="center">
					<tr>
					{$usercpnav}
					<td valign="top">
						<headtitle_big><b>Verwaltung deiner Relations</b></headtitle_big>
						Hier kannst du deine Beziehungen zu anderen Charakteren verwalten. Du kannst Kategorien erstellen oder löschen, Anfragen annehmen oder ablehnen.
						Anfragen die du selbst gestellt hast zurücknehmen und natürlich auch schon eingetragene Beziehungen wieder löschen oder ändern.<br /><br />
				
						<headtitle>Deine Kategorien Verwalten</headtitle><br />
						{$relas_ucp_cats}
						<form method="post">
								<input type="text" value="" name="toAddCat" style="margin-left:20px;width:100px;">
								<input type="submit" name="addCat" value="hinzufügen" id="rela_button">
						</form><br /><br />
							<headtitle_big>Offene Anfragen</headtitle_big><br /><br />
						{$relas_ucp_offene_nr}<br/><br/>
				
						<headtitle_big>eingetragene Relations</headtitle_big><br/>
						{$relas_ucp_accepted}
						
						{$relas_ucpAddNPC}
				
					</td>
					</tr>
					</table>
					{$footer}
					</body>
					</html>
		',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[6] = array(
        "title" => 'relas_ucpAddNPC',
        "template" => '
				   <headtitle_big> NPCs eintragen </headtitle_big><br /><br />
				
					<form action="" method="post">
						<div class="divbox_ucp_relas">
						<table>
							<tr>
								<td align="left">Name:</td>
								<td align="left"><input type="text" name="npcname" value="" id="rela_button" /></td>
							</tr>
							{$relas_npcimg}
							<tr>
								<td align="center" valign="top" width="1">
									Sortierung: <br />
									<input type="text" name="sort" style="width:30px;border-radius:5px;" id="rela_button" />
								</td>
								<td align="left">
									Kommentar:<br />
								<textarea name="kommentar" style="width: 100%; height:40px; background-color:#ffffff;" id="rela_button"></textarea>
								</td>
							</tr>
							<tr>
							<td colspan="2" align="left"><span class="smalltext">
								{$inputsRequest_own}
								</span>
							</td>
							</tr>
							<tr>
								<td colspan="2" align="center">
								<input type="submit" name="npceintragen" value="eintragen" id="rela_button" />
								</td>
							</tr>
						</table>
						</div>
					</form><br /><br />
    	',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[7] = array(
        "title" => 'relas_ucp_offene_eigene',
        "template" => '
                <input type="hidden" name="getrela" value="{$r_id}" />
                <input type="hidden" name="getfrom" value="{$r_fromid}" />
                <input type="hidden" name="getto" value="{$r_to}" />
                Du hast bei {$r_tolink} für die Kategorie <b> {$gefragte_kategorie}  </b> angefragt.  <br />
                <div class="divKomUCP">{$kommentar}</div>
                {$r_tolink} hat noch nicht reagiert. <br />
                <input type="submit" name="zuruecknehmen" value="zurücknehmen" class="rela_button send"/>
                <input type="submit" name="erinnern" value="erinnern" class="rela_button send"/>
                ',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[8] = array(
        "title" => 'relas_ucp_catbit',
        "template" => '
              <form method="post" action="">
				<tr>
				  <td valign="top" width="102px"><input type="hidden" value="{$kategorie_own}" name="getCat">{$kategorie_own}</td>
				  <td valign="top" align="left"><input type="submit" name="deleteCat" value="x" class="rela_button send"> </td>
				</tr>
			  </form>
             ',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[9] = array(
        "title" => 'relas_ucp_cats',
        "template" => '
                <table width="200px" class="table_own_cats_ucp">
                    {$relas_ucp_catbit}
                </table>
              ',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[10] = array(
        "title" => 'relas_showInProfil',
        "template" => '
               {$kat_titel}
             <div class="divbox_mem_relas">
                    <table width="100%">
                        <tr>
                        <td align="left" colspan="2" valign="top">
                            <span class="relas_profil_name">{$who_link}</span>
                        </td>
                        </tr>
                        <tr>
                        <td align="left" valign="top" width="{$tab_width}" >
                            {$who_img}
                        </td>
                        <td align="left" valign="top"><div class="relas_beschreibung">{$kommentar}</div></td>
                        </tr>
                </table>
            </div>
              ',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[11] = array(
        "title" => 'relas_ucp_nav',
        "template" => '
               <tr>
                <td class="trow1 smalltext">
                    <a href="usercp.php?action=relas_usercp">Relations</a>
                </td>
                </tr>
              ',
        "sid" => "-1",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    foreach ($template as $row) {
        $db->insert_query("templates", $row);
    }


    // Einstellungen
    $setting_group = array(
        'name' => 'relations',
        'title' => 'Relations',
        'description' => 'Einstellungen für Risus Relations Plugin',
        'disporder' => 7, // The order your setting group will display
        'isdefault' => 0
    );
    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        // NPC eintragbar
        'relas_npc' => array(
            'title' => 'NPCs?',
            'description' => 'Dürfen NPCs eingetragen werden?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 1
        ),
        // mit bild?
        'relas_npc_img' => array(
            'title' => 'NPC Bilder?',
            'description' => 'Dürfen für NPCs Bilder verwendet werden?',
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 2
        ),
        'relas_pmalert_change' => array(
            'title' => 'PM alert on change',
            'description' => 'Soll eine PN an den anderen Charakter geschickt werden, wenn etwas verändert wird?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 3
        ),
        'relas_pmalert_delete' => array(
            'title' => 'PM Alert on delete?',
            'description' => 'Soll eine PN an den anderen Charakter geschickt werden, wenn die Beziehung gelöscht wird?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 4
        ),
        'relas_img_guests' => array(
            'title' => 'Avatare für Gäste?',
            'description' => 'Sollen Gäste die Avatare sehen?',
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 5
        ),
        'relas_img_width' => array(
            'title' => 'Breite der Avatare im Profil?',
            'description' => 'Wie groß soll die Breite der Avatare der Charaktere im Profil sein? (nur zahl kein px)',
            'optionscode' => 'text',
            'value' => '35', // Default
            'disporder' => 6
        ),
        'relas_html' => array(
            'title' => 'Html erlauben?',
            'description' => 'Darf HTML benutzt werden?',
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 5
        ),
        'relas_mycode' => array(
            'title' => 'MyCode erlauben?',
            'description' => 'Darf MyCode benutzt werden?',
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 5
        )
    );

    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;
        $db->insert_query('settings', $setting);
    }
    rebuild_settings();
}

function relations_is_installed()
{
    global $db;
    if ($db->table_exists("relas")) {
        return true;
    }
    return false;
}

function relations_activate()
{
    global $db, $mybb;


    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#" . preg_quote('</fieldset>') . "#i", '</fieldset>{$relas_profil}');
}

function relations_deactivate()
{
    global $db;
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";

    find_replace_templatesets("member_profile", "#" . preg_quote('{$relas_profil}') . "#i", '');
}

/*
 * Hilfsfunktion
 * Kategorien des Users erhalten
 * Parameter: uid des users
 * return: Array mit den einzelnen Kategorien
 */
function get_Cats($uid)
{
    global $db, $mybb;
    $query_cats = $db->write_query("SELECT rela_cat FROM " . TABLE_PREFIX . "users WHERE uid = " . $uid . "");
    $kategorien_string = $db->fetch_field($query_cats, 'rela_cat');
    $kategorien = explode(',', $kategorien_string);
    array_shift($kategorien);
    array_pop($kategorien);
    return $kategorien;
}

/**
 * Hilfsfunktion build input radio UCP
 * Param
 * $kategorien_own = Array mit Kategorien
 * $check_kat = Aus Query -> zum check welche ist vorausgewählt?
 * $inputs_own um alle zusammenzufügen
 */
function relas_ucp_buildInput($kategorien_own, $check_kat, $inputs_own)
{
    //   $inputs_own="";
    foreach ($kategorien_own as $kategorie_own) {
        if ($check_kat == $kategorie_own) {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        $inputs_own .= '<input type="radio" class="rela_button kat" name="kategorie" value="' . $kategorie_own . '" ' . $checked . ' />' . $kategorie_own . ' | ';
    }
    return $inputs_own;
}

/*
 * Funktion relations_profile
 * Anzeige auf Profil des Users
 * Anfragen bei einem User
 */
$plugins->add_hook("member_profile_start", "relations_profile");
function relations_profile()
{
    global $db, $mybb, $session, $templates, $rela_anfrage, $relas_profil, $auf_profil, $inputs_own, $showrelas, $relas_ucpAddNPC;
    require_once MYBB_ROOT . "inc/datahandlers/pm.php";
    $pmhandler = new PMDataHandler();
    require_once MYBB_ROOT . "inc/class_parser.php";
    $parser = new postParser();
    $opt_mybbcode = intval($mybb->settings['relas_mycode']);
    $opt_html = intval($mybb->settings['relas_html']);
    $options = array(
        "allow_html" => $opt_html,
        "allow_mycode" => $opt_mybbcode,
        "allow_smilies" => 1,
        "allow_imgcode" => 0,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0,
    );

    $opt_img_guest = intval($mybb->settings['relas_img_guests']);
    $opt_npc_img = intval($mybb->settings['relas_npc_img']);

    //variablen leeren
    $rela_anfrage = $dieser_user = $auf_profil = $formularanzeige = '';
    //eigene user id
    $dieser_user = intval($mybb->user['uid']);
    //auf welchem Profil ist der User
    $auf_profil = intval($_REQUEST['uid']);

    $rela_anfrage = "";
    $inputs_own = "";

    //keine anfrage wenn auf eigenem Profil
    if ($dieser_user == $auf_profil) {
        $rela_anfrage = "";
    }

    //Kategorien input bauen:
    //Kategorien des users der online ist!
    $kategorien_own = get_Cats($dieser_user);
    //Input bauen
    foreach ($kategorien_own as $kategorie_own) {
        $inputs_own .= '<input type="radio" name="kategorie" value="' . $kategorie_own . '" class="rela_button kat" />' . $kategorie_own . " | ";
    }
    //ans Ende dranhängen:
    $inputs_own .= '<br/> <br/> Du kannst in deinem <a href="usercp.php?action=relas_usercp">User CP</a> Kategorien löschen oder hinzufügen.';
    
    $kat_text = htmlentities($mybb->get_input('kategorie'));

    //Anfrage stellen nur anzeigen wenn nicht gleicher Charakter.
    //TO DO: Noch Info, wenn man schon einmal angefragt hat?
    if ($dieser_user != $auf_profil) {
        $rela_anfrage_tit = '<tr><td class="mem_name" colspan="2">
		<div class="tracker_line"><span class="tracker_month">Anfrage stellen</span></div>
		</td></tr>';
        eval("\$rela_anfrage .= \"" . $templates->get("relas_memberprofil_anfrage") . "\";");
    }

    //Gast darf keine anfrage stellen
    if ($mybb->user['uid'] == 0) {
        $rela_anfrage = "";
        $rela_anfrage_tit = "";
    }

    //Kategorien bekommen und anzeigen von Charakter auf dessen Profil man ist
    $kategorien = get_Cats($auf_profil);

    //Ausgabe der Kategorien
    foreach ($kategorien as $kategorie) {
        $counter = 0;

        //Die Charaktere anzeigen zu denen eine Rela besteht
        $get_relation = $db->write_query("
				SELECT * FROM " . TABLE_PREFIX . "relas WHERE
				(r_from = '$auf_profil' AND r_accepted = '1')
				AND r_kategorie = '" . $kategorie . "'
				ORDER BY r_sort
				");
        //LEFT JOIN mybb_userfields ON mybb_users.uid = mybb_userfields.ufid
        if ($db->num_rows($get_relation) > 0) {
            while ($get_relas = $db->fetch_array($get_relation)) {
                //Counter nötig um Kategorietitel(nur einmal) anzuzeigen
                $counter++;
                //variable wird dynamisch gebaut zu -> $relas_mem_kategoriename
                ${'relas_mem_' . $kategorie} = "";

                //alle infos des User zu dem Rela besteht bekommen
                $who = get_user($get_relas['r_to']);



                //infos sowohl npc als auch user
                $r_from = $get_relas['r_to'];
                $kat = htmlspecialchars($get_relas['r_kategorie']);
                $kommentar = $parser->parse_message($get_relas['r_kommentar'], $options);
                $sort = intval($get_relas['r_sort']);
                $r_id = intval($get_relas['r_id']);
                $rnpcname = htmlspecialchars($get_relas['r_npcname']);

                $img_width = intval($mybb->settings['relas_img_width']);
                $tab_width = $img_width + 5;

                //NPC oder User
                $r_npc = $get_relas['r_npc'];
                if ($r_npc == 1) {
                    $who_link = $rnpcname;
                    //Wenn Bild bei NPC erlaubt
                    if ($opt_npc_img == 0) {
                        $tab_width = "1px";
                        $who_img = "";
                    } else {
                        $who_img = '<img src="' . $get_relas['r_npcimg'] . '"  width="' . $img_width . '"/>';
                    }
                } else {
                    //userinfo wenn registriert: 
                    $who_link = build_profile_link($who['username'], $who['uid'], '_blank');
                    $who_id = $who['uid'];
                    $who_img = '<img src="' . $who['avatar'] . '"  width="' . $img_width . '"/>';
                }

                //Gäste dürfen keine bilder sehen
                if ($mybb->user['uid'] == 0 && $opt_img_guest == 0) {
                    $who_img = "";
                    $tab_width = "1px";
                }

                //Sorgt dafür, dass die Kategorie nur einmal als Überschrift ausgeben wird.
                if ($counter == 1) {
                    ${$kategorie . 'titel'} = "<headtitle_big>" . htmlspecialchars($kategorie) . "</headtitle_big>";
                } else {
                    ${$kategorie . 'titel'} = "";
                }
                $kat_titel = ${$kategorie . 'titel'};

                eval("\$showrelas .= \"" . $templates->get("relas_showInProfil") . "\";");
            }
        }
    }

    //Daten speichern bei Anfrage
    if ($mybb->get_input('anfragen') && $mybb->user['uid'] != 0) {

        $rela_anfrage = array(
            "r_from" => intval($dieser_user),
            "r_to" => intval($auf_profil),
            "r_kategorie" => $db->escape_string($mybb->get_input('kategorie')),
            "r_kommentar" => $db->escape_string($mybb->get_input('kommentar')),
            "r_sort" => intval($mybb->get_input('sort')),
            "r_accepted" => 0,
            "r_npc" => 0
        );

        $to_array = get_user($dieser_user);
        $fromlink = build_profile_link($to_array['username'], $to_array['uid'], '_blank');
        $kat = $mybb->get_input('kategorie');
        if (empty($kat)) {
            echo "<script>alert('Bitte eine Kategorie auswählen')</script>";
            redirect("member.php?action=profile&uid={$auf_profil}");
        } else {
            //eintragen
            $db->insert_query("relas", $rela_anfrage);
            if (isset($mybb->input['kommentar'])) {
                $pmkom = str_replace('\r\n', "\r\n", $db->escape_string(nl2br($mybb->input['kommentar'])));
            } else {
                $pmkom = "Keine Beschreibung";
            }
            //PN losschicken
            $pm_change = array(
                "subject" => "Relationsanfrage",
                "message" => $fromlink . ' hat dir eine Relationsanfrage gestellt. <br />
                [QUOTE]' . $pmkom . ' 
                [/QUOTE]<br/>
                Schau in dein <a href="usercp.php?action=relas_usercp">User CP</a> um sie anzunehmen oder abzulehnen.',
                //from: wer hat die anfrage gestellt
                "fromid" => $dieser_user,
                //to: wer muss die anfrage bestätigen, also auf wessen profil waren wir
                "toid" => $auf_profil,
                "icon" => "",
                "do" => "",
                "pmid" => "",
            );

            $pm_change['options'] = array(
                'signature' => '0',
                'savecopy' => '0',
                'disablesmilies' => '0',
                'readreceipt' => '0',
            );

            if (isset($session)) {
                $pm_change['ipaddress'] = $session->packedip;
            }

            $pmhandler->set_data($pm_change);
            if (!$pmhandler->validate_pm()) {
                return false;
            } else {
                $pmhandler->insert_pm();
            }

            redirect("member.php?action=profile&uid={$auf_profil}");
        }
    }
    eval("\$relas_profil .= \"" . $templates->get("relas_memberprofil") . "\";");
}


/*
 * Fügt die Verwaltung der Relas ins user CP ein
 */
$plugins->add_hook("usercp_menu", "relas_usercp_menu");
function relas_usercp_menu()
{
    global $templates;
    $relas_ucp_nav = "";
    eval("\$relas_ucp_nav .= \"" . $templates->get("relas_ucp_nav") . "\";");
    $templates->cache["usercp_nav_misc"] = str_replace("<tbody style=\"{\$collapsed['usercpmisc_e']}\" id=\"usercpmisc_e\">", "<tbody style=\"{\$collapsed['usercpmisc_e']}\" id=\"usercpmisc_e\">{$relas_ucp_nav}", $templates->cache["usercp_nav_misc"]);
}

/*
 * Verwaltung der Relations im User CP
 * Kategorien verwalten
 * NPCs hinzufügen
 * Akzeptieren/Ablehnen von Beziehungen
 * Einen anderen Charakter erinnern
 * Löschen und ändern */
$plugins->add_hook("usercp_start", "relas_usercp");
function relas_usercp()
{
    global $mybb, $db, $templates, $anfragen, $relas_ucpadmin,
        $angefragt, $cache, $templates, $themes, $headerinclude,
        $header, $footer, $usercpnav, $relas_ucp_offene,
        $relas_ucp_accepted, $inputs_own, $titel,
        $list_own_cats, $inputsRequest_own,
        $relas_ucp_cats, $relas_ucp_catbit, $session;
    $mybb->input['action'] = $mybb->get_input('action');
    if ($mybb->input['action'] != "relas_usercp") {
        return false;
    }

    require_once MYBB_ROOT . "inc/datahandlers/pm.php";
    $pmhandler = new PMDataHandler();

    //Variablen initialisieren
    $relas_usercp = $rela_anfrage = $dieser_user = $auf_profil = $status_text = $formularanzeige = '';

    //eigene user id
    $dieser_user = intval($mybb->user['uid']);
    /* Einstellungen aus dem acp */
    $opt_npc = intval($mybb->settings['relas_npc']);
    $opt_npc_img = intval($mybb->settings['relas_npc_img']);
    $opt_pmchange = intval($mybb->settings['relas_pmalert_change']);
    $opt_pmdelete = intval($mybb->settings['relas_pmalert_delete']);

    $reaktion = '';

    //Ausgabe von vorhandenen Relas
    $ucp_akzeptiert = $db->write_query("
		SELECT * FROM " . TABLE_PREFIX . "relas WHERE
		(r_from = '$dieser_user' AND r_accepted = '1')
		OR
		(r_from = '$dieser_user' AND r_npc = '1')
		ORDER BY r_kategorie, r_sort");

    //Kategorien input bauen: Kategorien des users der online ist!
    $kategorien_own = get_Cats($dieser_user);

    /* Anzeige und bauen der eigenen Kategorien */
    foreach ($kategorien_own as $kategorie_own) {

        eval("\$relas_ucp_catbit .= \"" . $templates->get("relas_ucp_catbit") . "\";");
    }

    eval("\$relas_ucp_cats = \"" . $templates->get("relas_ucp_cats") . "\";");
    //    $list_own_cats = '<table width="200px" class="table_own_cats_ucp">' . $list_own_cats_li . '</table>';

    /* eigene Kategorien verwalten und anzeige von akzeptierten Charas */
    while ($get_accepted = $db->fetch_array($ucp_akzeptiert)) {
        $catFromLastEntry = $kategorie;
        $list_own_cats_li = $inputs_own = $checked = $feld_npcname = $r_npc = $rnpcname = $r_id = $sort = $kommentar = $kategorie = $who_img = $who_link = $r_from = $who = $fam_check_ucp = $liebe_check_ucp = $freunde_check_ucp = $bekannte_check_ucp = $ungemocht_check_ucp = $sonst_check_ucp = "";

        $who = get_user($get_accepted['r_to']);
        $r_from = $get_accepted['r_from'];
        $who_link = build_profile_link($who['username'], $who['uid'], '_blank');
        $who_id = $who['uid'];
        $who_img = $who['avatar'];
        if (isset($get_accepted['r_kategorie'])) {
            $get_accepted['r_kategorie'] =  $db->escape_string($get_accepted['r_kategorie']);
        }
        if ($catFromLastEntry != $kategorie) {
            $titel = "<br /><headtitle_big>" . $kategorie . "</headtitle_big>";
        } else {
            $titel = "";
        }

        $kommentar = htmlspecialchars($get_accepted['r_kommentar']);

        $sort = intval($get_accepted['r_sort']);
        $r_id = $get_accepted['r_id'];
        $rnpcname = htmlspecialchars($get_accepted['r_npcname']);
        $r_npc = $get_accepted['r_npc'];
        $rnpcimg = $get_accepted['r_npcimg'];

        /*rpc name und bild ändern*/
        if ($r_npc == 1) {
            $feld_npcname = '
				<tr><td align="left" colspan="2">
				NPC Name: 
				<input type="text" name="npcname" class ="rela_button npcname" value="' . $rnpcname . '">
				</td></tr>';
            $who_link = $rnpcname;
            /*npc image ändern*/
            if ($opt_npc_img == 1) {
                $feld_npcimg =  '
				<tr><td align="left" colspan="2">
				NPC Bild:
				<input type="text" name="npcimg" class ="rela_button npcimg" value="' . $rnpcimg . '">
				</td></tr>';
            }
        }
        /*radio input Kategorien bauen -> Hilfsfunktion aufrufen*/
        $inputs_own = relas_ucp_buildInput($kategorien_own, $kategorie, $inputs_own);
        eval("\$relas_ucp_accepted .= \"" . $templates->get("relas_accepted") . "\";");
        $feld_npcimg = "";
    }

    // eigene Kategorien löschen
    if ($mybb->get_input('deleteCat')) {
        $db->write_query("UPDATE " . TABLE_PREFIX . "users SET rela_cat = replace(rela_cat,'" . $mybb->input['getCat'] . ",','') WHERE uid = " . $dieser_user . "");

        //Erinnerung an die User, die Kategorien zu aktualisieren
        echo "<script>alert('Denk daran die Kategorien der Charaktere der Gelöschten zu aktualisieren. Sonst werden sie nicht im Profil angezeigt.')</script>";
        redirect('usercp.php?action=relas_usercp');
    }

    //eigene Kategorie hinzufügen
    if ($mybb->get_input('addCat') && (!empty($mybb->input['toAddCat']) || !isset($mybb->input['toAddCat']))) {
        $db->write_query("UPDATE " . TABLE_PREFIX . "users SET rela_cat = concat(rela_cat,'" . $mybb->input['toAddCat'] . ",') WHERE uid = " . $dieser_user . "");
        redirect('usercp.php?action=relas_usercp');
    } elseif ($mybb->get_input('addCat') &&  (empty($mybb->input['toAddCat']) || isset($mybb->input['toAddCat']))) {
        echo "<script>alert('Du kannst keine leeren Kategorien erstellen.')";
        redirect('usercp.php?action=relas_usercp');
    }

    //akzeptierte Charaktere ändern
    if (isset($mybb->input['change'])) {
        //id setzen
        $relaid = intval($mybb->get_input('getrela'));
        $upd_kat = $db->escape_string($mybb->get_input('kategorie'));
        $upd_kommentar = $db->escape_string($mybb->get_input('kommentar'));
        if ($upd_kommentar) {
            $upd_kommentar = str_replace('\r\n', "\r\n", $db->escape_string(nl2br($upd_kommentar)));
        }
        $upd_sort = intval($mybb->get_input('sort'));
        if (!isset($mybb->input['npcname'])) {
            $npcname = $db->escape_string($mybb->input['npcname']);
        }
        if (!isset($mybb->input['npcimg'])) {
            $npcimg = $db->escape_string($mybb->input['npcimg']);
        }
        $query_npc = $db->simple_select('relas', 'r_npc', "r_id ='" . $relaid . "'", array('LIMIT' => 1));
        $checknpc = $db->fetch_field($query_npc, 'r_npc');

        //PN nur wenn eingestellt
        if ($opt_pmchange == 1 && $checknpc == 0) {
            $pm_reanfrage = array(
                "subject" => "Relations Änderung",
                "message" =>
                'Ich hab etwas an unserem Relationseintrag geändert.
                [quote]' . $upd_kommentar . '[/quote]
                <br /> Du kannst es dir in deinem <a href="usercp.php?action=relas_usercp">User CP</a> anschauen.
                ',
                //to: wer muss die anfrage bestätigen
                "fromid" => intval($mybb->get_input('getfrom')),
                //from: wer hat die anfrage gestellt
                "toid" => intval($mybb->get_input('getto')),
                "icon" => "",
                "do" => "",
                "pmid" => "",
            );

            $pm_reanfrage['options'] = array(
                'signature' => '0',
                'savecopy' => '0',
                'disablesmilies' => '0',
                'readreceipt' => '0',
            );

            if (isset($session)) {
                $pm_reanfrage['ipaddress'] = $session->packedip;
            }

            $pmhandler->set_data($pm_reanfrage);
            if (!$pmhandler->validate_pm())
                return false;
            else {
                $pmhandler->insert_pm();
            }
        }
        $db->query("UPDATE " . TABLE_PREFIX . "relas SET r_kategorie = '" . $upd_kat . "', r_kommentar = '" . $upd_kommentar . "', r_sort = '" . $upd_sort . "', r_npcname = '" . $npcname . "', r_npcimg ='" . $npcimg . "' WHERE r_id = " . $relaid . "");
        echo "<meta http-equiv='refresh' content='0'>";
    }

    //akzeptiere Charaktere löschen
    if (isset($mybb->input['delete'])) {
        //PN nur wenn eingestellt
        $relaid = intval($mybb->get_input('getrela'));

        $query_npc = $db->simple_select('relas', '*', "r_id ='" . $relaid . "'", array('LIMIT' => 1));
        $checknpc = $db->fetch_field($query_npc, 'r_npc');
        $checkdeleted = $db->fetch_field($db->simple_select('relas', '*', "r_id ='" . $relaid . "'", array('LIMIT' => 1)), 'r_to');
        $query_guesterror = $db->simple_select('users', '*', "uid ='" . $checkdeleted . "'");

        if ($db->num_rows($query_guesterror) == 0) {
            $db->write_query("DELETE FROM " . TABLE_PREFIX . "relas WHERE r_id = '" . $relaid . "'");
            redirect('usercp.php?action=relas_usercp');
        }
        if ($opt_pmdelete == 1 && $checknpc == 0) {
            //id setzen

            $pm_reanfrage = array(
                "subject" => "Relations - Löschung",
                "message" =>
                'Ich hab unseren Eintrag gelöscht.',
                //to: wer muss die anfrage bestätigen
                "fromid" => intval($mybb->get_input('getfrom')),
                //from: wer hat die anfrage gestellt
                "toid" => intval($mybb->get_input('getto')),
                "icon" => "",
                "do" => "",
                "pmid" => "",
            );
            $pm_reanfrage['options'] = array(
                'signature' => '0',
                'savecopy' => '0',
                'disablesmilies' => '0',
                'readreceipt' => '0',
            );

            if (isset($session)) {
                $pm_reanfrage['ipaddress'] = $session->packedip;
            }
            // $pmhandler->admin_override = true;
            $pmhandler->set_data($pm_reanfrage);
            if (!$pmhandler->validate_pm()) {
                return false;
            } else {
                $pmhandler->insert_pm();
            }
        }
        $db->write_query("DELETE FROM " . TABLE_PREFIX . "relas WHERE r_id = '" . $relaid . "'");
        redirect('usercp.php?action=relas_usercp');
    }

    $reaktion = "";

    /********
     *  Ausgabe Anfrage - offene Anfragen von anderen Charas
     *******/
    $ucp_offeneanfragen = $db->write_query("
		SELECT * FROM " . TABLE_PREFIX . "relas WHERE
			(r_to = '$dieser_user' AND r_accepted = '0')
			OR
			(r_from = '$dieser_user' AND r_accepted = '0')
		ORDER BY r_to, r_accepted, r_sort");
    $relas_ucp_offene_nr = "";
    while ($get_anfrage = $db->fetch_array($ucp_offeneanfragen)) {
        $anfragender = get_user($get_anfrage['r_from']);
        $angefragt = get_user($get_anfrage['r_to']);
        $r_id = $get_anfrage['r_id'];
        $r_to = $get_anfrage['r_to'];
        $r_fromid = $get_anfrage['r_from'];
        $r_tolink = build_profile_link($angefragt['username'], $angefragt['uid'], '_blank');;
        $r_from = build_profile_link($anfragender['username'], $anfragender['uid'], '_blank');
        $gefragte_kategorie = $get_anfrage['r_kategorie'];
        $kommentar = htmlspecialchars($get_anfrage['r_kommentar']);
        $r_status = $get_anfrage['r_accepted'];
        $inputsRequest_own = "";

        /* Hilfsfunktion aufrufen: Stelle die radiobuttons für auswählbaren Kategorien zusammen*/
        $inputsRequest_own = relas_ucp_buildInput($kategorien_own, $gefragte_kategorie, $inputsRequest_own);
        $reaktion = "";

        if ($get_anfrage['r_from'] != $dieser_user) {
            /* offene anfragen von anderen Charas*/
            eval("\$reaktion .= \"" . $templates->get("relas_anfragen") . "\";");
        } else if ($get_anfrage['r_from'] == $dieser_user) {
            /*Eigene Anfrage - noch nicht beantwortet*/
            eval("\$reaktion .= \"" . $templates->get("relas_ucp_offene_eigene") . "\";");
        }
        eval("\$relas_ucp_offene_nr .= \"" . $templates->get("relas_ucp_offene_nr") . "\";");
    }

    /* Eine bekommene Anfrage bestätigen und direkt eigene Informationen losschicken */
    if (isset($mybb->input['reeintragen'])) {
        $relaid = intval($mybb->get_input('getrela'));
        //von wem wurde die anfrage geshickt?
        $from = intval($mybb->get_input('getfrom'));
        //to der der bestätigen muss - zu wem wurde die anfrage geschickt?
        $to = intval($mybb->get_input('getto'));
        $to_array = get_user($to);
        $tolink = build_profile_link($to_array['username'], $to_array['uid'], '_blank');
        if (isset($mybb->input['kategorie'])) {
            $mybb->input['kategorie'] = $db->escape_string($mybb->input['kategorie']);
        }
        if (isset($mybb->input['kommentar'])) {
            $mybb->input['kommentar'] =  $db->escape_string($mybb->input['kommentar']);
        }

        $db->write_query("UPDATE " . TABLE_PREFIX . "relas SET r_accepted = 1 WHERE r_id = '" . $relaid . "'");
        $rela_anfrage = array(
            "r_from" => $to,
            "r_to" => $from,
            "r_kategorie" => $db->escape_string($mybb->get_input('kategorie')),
            "r_kommentar" => $db->escape_string($mybb->get_input('kommentar')),
            "r_sort" => intval($mybb->get_input('sort')),
            "r_accepted" => 1,
            "r_npc" => 0
        );

        $db->insert_query("relas", $rela_anfrage);
        if (isset($mybb->input['kommentar'])) {
            $mybb->input['kommentar'] =  $db->escape_string(nl2br($mybb->input['kommentar']));
        }
        $pm_kommentar = str_replace('\r\n', "\r\n", $mybb->input['kommentar']);
        $pm_reanfrage = array(
            "subject" => "Relations Anfrage",
            "message" =>
            $tolink . ' hat deine Anfrage bestätigt und dich auch eingetragen.
                [quote]' . $pm_kommentar . '[/quote]
                <br /> Du kannst es dir in deinem <a href="usercp.php?action=relas_usercp">User CP</a> anschauen.
                ',
            //to: wer muss die anfrage bestätigen
            "fromid" => $to,
            //from: wer hat die anfrage gestellt
            "toid" => $from,
            "icon" => "",
            "do" => "",
            "pmid" => "",
        );
        $pm_reanfrage['options'] = array(
            'signature' => '0',
            'savecopy' => '0',
            'disablesmilies' => '0',
            'readreceipt' => '0',
        );
        if (isset($session)) {
            $pm_reanfrage['ipaddress'] = $session->packedip;
        }
        // $pmhandler->admin_override = true;
        $pmhandler->set_data($pm_reanfrage);
        if (!$pmhandler->validate_pm())
            return false;
        else {
            $pmhandler->insert_pm();
        }
        redirect('usercp.php?action=relas_usercp');
    }

    /* eine bekommene Anfrage ablehnen.*/
    if (isset($mybb->input['ablehnen'])) {
        $relaid = $mybb->get_input('getrela');
        //von wem wurde die anfrage geshickt?
        $from = $mybb->get_input('getfrom');
        //to der der bestätigen muss - zu wem wurde die anfrage geschickt?
        $to = $mybb->get_input('getto');
        $to_array = get_user($mybb->input['getto']);
        $tolink = build_profile_link($to_array['username'], $to_array['uid'], '_blank');

        $db->query("DELETE FROM " . TABLE_PREFIX . "relas WHERE r_id = '" . $relaid . "'");
        $pm_abgelehnt = array(
            "subject" => "Relationanfrage",
            "message" => $tolink . " hat deine Anfrage abgelehnt. Wenn du wissen willst wieso, antworte einfach auf die PN und frage nach :) ",
            //to: wer muss die anfrage bestätigen
            "fromid" => $to,
            //from: wer hat die anfrage gestellt
            "toid" => $from,
            "icon" => "",
            "do" => "",
            "pmid" => "",
        );

        $pm_abgelehnt['options'] = array(
            'signature' => '0',
            'savecopy' => '0',
            'disablesmilies' => '0',
            'readreceipt' => '0',
        );

        if (isset($session)) {
            $pm_abgelehnt['ipaddress'] = $session->packedip;
        }
        // $pmhandler->admin_override = true;
        $pmhandler->set_data($pm_abgelehnt);
        if (!$pmhandler->validate_pm())
            return false;
        else {
            $pmhandler->insert_pm();
        }
        redirect('usercp.php?action=relas_usercp');
    }

    /* eine eigene Anfrage zurücknehmen.*/
    if (isset($mybb->input['zuruecknehmen'])) {
        $relaid = $mybb->get_input('getrela');
        //von wem wurde die anfrage geshickt?
        $from = $mybb->get_input('getfrom');
        //to der der bestätigen muss - zu wem wurde die anfrage geschickt?
        $to = $mybb->get_input('getto');
        $to_array = get_user($mybb->get_input('getto'));
        $tolink = build_profile_link($to_array['username'], $to_array['uid'], '_blank');

        $db->write_query("DELETE FROM " . TABLE_PREFIX . "relas WHERE r_id = '" . $relaid . "'");
        $pm_zurueck = array(
            "subject" => "Relations Anfrage",
            "message" => $tolink . " hat seine Anfrage wieder zurückgenommen. Wenn du magst kannst du einfach auf die PN antworten und fragen wieso.",
            //to: wer muss die anfrage bestätigen
            "fromid" => $to,
            //from: wer hat die anfrage gestellt
            "toid" => $from,
            "icon" => "",
            "do" => "",
            "pmid" => "",
        );
        $pm_zurueck['options'] = array(
            'signature' => '0',
            'savecopy' => '0',
            'disablesmilies' => '0',
            'readreceipt' => '0',
        );
        if (isset($session)) {
            $pm_zurueck['ipaddress'] = $session->packedip;
        }
        $pmhandler->set_data($pm_zurueck);
        if (!$pmhandler->validate_pm())
            return false;
        else {
            $pmhandler->insert_pm();
        }
        redirect('usercp.php?action=relas_usercp');
    }

    /*Erinnern anderer Charaktere an schon gestellte Rela Anfrage */
    if (isset($mybb->input['erinnern'])) {
        $to = $mybb->get_input('getto');
        $from = $mybb->get_input('getfrom');
        $to_array = get_user($from);
        $fromlink = build_profile_link($to_array['username'], $to_array['uid'], '_blank');

        $pm_erinnerung = array(
            "subject" => "Erinnerung: Relations Anfrage",
            "message" => $fromlink . ' hat dir eine Anfrage geschickt. Du hast noch nicht reagiert.
            Bitte schau einmal in dein <a href="usercp.php?action=relas_usercp">User CP</a>',
            "fromid" => $from,
            "toid" => $to,
            "icon" => "",
            "do" => "",
            "pmid" => "",
        );
        $pm_erinnerung['options'] = array(
            'signature' => '0',
            'savecopy' => '0',
            'disablesmilies' => '0',
            'readreceipt' => '0',
        );
        if (isset($session)) {
            $pm_erinnerung['ipaddress'] = $session->packedip;
        }
        // $pmhandler->admin_override = true;
        $pmhandler->set_data($pm_erinnerung);
        if (!$pmhandler->validate_pm())
            return false;
        else {
            $pmhandler->insert_pm();
        }
        redirect('usercp.php?action=relas_usercp');
    }

    $inputsRequest_own = "";
    $inputsRequest_own = relas_ucp_buildInput($kategorien_own, $gefragte_kategorie, $inputsRequest_own);

    //Settings npc -> get template
    if ($opt_npc_img == 1) {
        $relas_npcimg = '
            <tr>
				<td align="left">Bild:</td>
				<td align="left"><input type="text" name="npcimg" value="" class="rela_button npcimg" /></td>
			</tr>';
    } else {
        $relas_npcimg = "";
    }
    $relas_ucpAddNPC = "";
    if ($opt_npc == 1) {
        eval("\$relas_ucpAddNPC .= \"" . $templates->get("relas_ucpAddNPC") . "\";");
    }
    /* NPCs eintragen */
    if ($mybb->get_input('npceintragen')) {
        $kategorie_test = $mybb->get_input('kategorie');
        if ($kategorie_test == '') {
            echo "<script>alert('Bitte eine Kategorie auswählen')</script></font>";
            redirect('usercp.php?action=relas_usercp');
        }
        if (isset($mybb->input['npcname'])) {
            $mybb->input['npcname'] = $db->escape_string($mybb->input['npcname']);
        }
        if (isset($mybb->input['kategorie'])) {
            $mybb->input['kategorie'] = $db->escape_string($mybb->input['kategorie']);
        }
        if (isset($mybb->input['kommentar'])) {
            $mybb->input['kommentar'] = $db->escape_string($mybb->input['kommentar']);
        }
        if (isset($mybb->input['npcimg'])) {
            $mybb->input['npcimg'] = $db->escape_string($mybb->input['npcimg']);
        }
        $rela_anfrage = array(
            "r_npcname" => $db->escape_string($mybb->get_input('npcname')),
            "r_to" => $dieser_user,
            "r_from" => $dieser_user,
            "r_kategorie" => $db->escape_string($mybb->get_input('kategorie')),
            "r_kommentar" => $db->escape_string($mybb->get_input('kommentar')),
            "r_sort" => intval($mybb->get_input('sort', MyBB::INPUT_INT)),
            "r_accepted" => 1,
            "r_npc" => 1,
            "r_npcimg" => $db->escape_string($mybb->get_input('npcimg'))
        );

        $db->insert_query("relas", $rela_anfrage);
        redirect('usercp.php?action=relas_usercp');
    }

    eval("\$relas_usercp = \"" . $templates->get("relas_usercp") . "\";");
    output_page($relas_usercp);
}

/*
 *  Verwaltung der Defaults im Tool Menü des ACP hinzufügen
 *  freien index finden
 */
$plugins->add_hook("admin_tools_menu", "relationstools_menu");
function relationstools_menu($sub_menu)
{
    $key = count($sub_menu) * 10 + 10; /* We need a unique key here so this works well. */
    $sub_menu[$key] = array(
        'id'    => 'relations',
        'title'    => 'Relations Verwaltung',
        'link'    => 'index.php?module=tools-relations'
    );
    return $sub_menu;
}

$plugins->add_hook("admin_tools_action_handler", "relationstools_action_handler");
function relationstools_action_handler($actions)
{
    $actions['relations'] = array('active' => 'relations', 'file' => 'relations.php');
    return $actions;
}

/**
 * Was passiert wenn ein User gelöscht wird
 * Relas bei anderen zu npc umtragen
 * die relas des users löschen
 */
$plugins->add_hook("admin_user_users_delete_commit_end", "user_delete");
function user_delete()
{
    global $db, $cache, $mybb, $user;
    $todelete = (int)$user['uid'];
    $username = $db->escape_string($user['username']);
    $update_other_relas = array(
        'r_to' => 0,
        'r_npc' => 1,
        'r_npcname' => $username
    );
    //   $db->update_query("{name_of_table}", $update_array, "WHERE {options}");
    $db->update_query('relas', $update_other_relas, "r_to='" . (int)$user['uid'] . "'");
    $db->delete_query('relas', "r_from = " . (int)$user['uid'] . "");
}
