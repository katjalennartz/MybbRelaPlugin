<?php
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
global $mybb, $lang, $db;

$page->add_breadcrumb_item("Relations - Administration", "index.php?module=tools-relations");
//$page->output_header("Relations - Administration");
/**
 * Set Defaultvalues for categories
 */
if(!$mybb->input['action']) {
    if($mybb->request_method == "post")
    {
        if(isset($mybb->input['do_setDefault']))
        {
            $default_cat = $db->escape_string($mybb->get_input('rela_cat', MyBB::INPUT_STRING));
            $db->query("UPDATE " . TABLE_PREFIX . "users SET rela_cat = '" . $default_cat . "'");
            admin_redirect("index.php?module=tools-relations");
        }
    }

    $page->output_header("Testausgabe");

    $sub_tabs['setDefault'] = array(
        'title' => "Relations",
        'link' => "index.php?module=tools-relations",
        'description' => "Hier kannst du die Default Kategorien neu wählen."
    );

    $page->output_nav_tabs($sub_tabs, 'setDefault');

    $form = new Form("index.php?module=tools-relations", "post");

    $form_container = new FormContainer("Relations Verwaltung");
    $form_container->output_row_header("Defaultwerte setzen");
    $form_container->output_row_header("Kategorien", array('width' => 350));
    $form_container->output_row_header("&nbsp;");

    $form_container->output_cell("<label>Achtung</label> <div class=\"description\">Bitte mit Vorsicht benutzen. Wenn du die Defaults setzt werden die Kategorien aller Benutzer überschrieben!
Anwendung: Kategorien mit , getrennt ins Textfeld schreiben. Achtung, vorne und hinten muss auch ein Komma stehen.<br/>
<b>Beispiel:</b> ,Familie,Freunde,Liebe,Bekannte,Ungemocht,Sonstiges,</div>");

    $form_container->output_cell($form->generate_text_box("rela_cat", "", array('style' => 'width: 250px;')));
    $form_container->output_cell($form->generate_submit_button($lang->go, array("name" => "do_setDefault")));
    $form_container->construct_row();

    $form_container->end();
    $form->end();
    $page->output_footer();
}





