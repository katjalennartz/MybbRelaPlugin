Upgrade zu 1.1.
-> gelöschte User werden von nun an zum NPC umgetragen
-> MyCode und html kann aktiviert werden

To Do:

ZUERST!!! Altes Plugin Deaktivieren
DANACH:
Dateien hochladen und ersetzen

Plugin wieder aktivieren.

-> In den Einstellungen einstellen ob html/mycode aktiviert sein soll oder nicht.


Schon gelöschte User zu NPC umtragen:

in die datenbank einloggen (phpmyadmin) -> sql ausführen
(mybb_ mit tabellenpräfix ersetzen)

DELETE:  
DELETE FROM mybb_relas WHERE r_from NOT IN (SELECT uid FROM mybb_users) AND uid != 0

UPDATE: (die relas bei anderen charas zu npc umtragen)
UPDATE mybb_relas SET r_to = 0 and r_npc = 1 WHERE r_to NOT IN (SELECT uid FROM mybb_users)

___________________________________________

Relations 1.0 von risuena

Beschreibung:
	Relations im Profil anzeigen
	Im Profil eines Charakters anfragen
	PN bei Anfrage
	Bestätigung nötig. 
	Verwaltung im UCP
	Eigene Kategorien anlegen, oder vorhandene löschen
	Annehmen von Relas
	Erinnern an eine gestellte Anfrage
	Zurücknehmen einer gestellten Anfrage
	NPCs Erstellen (je nach Admineinstellung mit oder ohne Bild)
	Ändern Relas (Pn wenn eingestellt)
	Löschen von Relas (PN wenn eingestellt) 
	
	Admin Einstellungen
		Default Kategorien setzen (ACP -> Tools -> Relations Verwaltung. Achtung Kategorien der User werden überschrieben!) 
		Normale Einstellungen (Konfiguration -> Einstellungen)
		NPCs Ja/Nein
		NPCs Bilder Ja/Nein
		PM bei Änderungen Ja/Nein
		PM bei Löschung Ja/Nein
		Avatar Anzeige für Gäste Ja/Nein
		Breite der Avatare im Profil 
		
		
CSS fürs Plugin
minimal Version - ihr könnt relativ viel hinzufügen und ändern. Ich habe mich bemüht das Plugin so zu schreiben, dass ihr die Klassen gut Stylen könnt.
-> Tipp... geht mit dem Untersuchenwerkzeug auf Entdeckung ;) 
Ihr könnt für jeden Style ein neues CSS in eurer Theme verwaltung, oder das ganze extern einbinden. Wie ihr wollt. 

headtitle_big {
    font-size: 1.5em;
    line-height: 0.9;
    display: block;
    border-bottom: 3px solid black;
}

.relas_beschreibung {
    font-size: 0.8em;
}

.divbox_mem_relas {
    width: 250px;
    height: 100px;
    overflow: auto;
    float: left;
}

.relas_profil_name{
    font-size: 1.2em;
}

input.rela.sort {
    width: 20px;
}

.textarea_kommentar{    
    width:200px;
}
/*shows inputbuttons when tabs are used*/
input.rela_button.kat {
    display: inherit;
    position: relative;
}


Import von alten Relas aus anderen Plugins:

Relas aus Jules Plugin importieren:
Importieren von daten aus Jules Rela Plugin:

1. to do first Immer:
-> Admin CP -> Tools -> Relations Verwaltung
hier die alten Kategorien mit Komma getrennt eintragen
(also zum Beispiel: ,neutral,positiv,negativ, )


2. Query ausführen: (z.b. über phpmyadmin -> sql)
ACHTUNG evt. Tableprefix anpassen! 

Für Jules Plugin Standard 
	INSERT INTO `mybb_relas` (r_from, r_to, r_kategorie, r_kommentar,r_accepted) SELECT suid,ruid,type,relation,1 FROM `mybb_rprelations`

Mit Erweiterung Text (reltext)
	INSERT INTO `mybb_relas` (r_from, r_to, r_kategorie, r_kommentar,r_accepted) SELECT suid,ruid,type,CONCAT(relation,' - ',reltext),1 FROM `mybb_rprelations`

Mit NPCS und Erweiterung Text:
	INSERT INTO `mybb_relas` (r_from, r_to, r_kategorie, r_kommentar,r_npc,r_npcname,r_accepted) SELECT suid,ruid,type,CONCAT(npcinfo, ' - ', relation,' - ',reltext), IF(npcname != "", "1", "0"),npcname,1 FROM `mybb_rprelations`

Mit NPC Ohne Erweiterung Text 
	INSERT INTO `mybb_relas` (r_from, r_to, r_kategorie, r_kommentar,r_npc,r_npcname,r_accepted) SELECT suid,ruid,type,CONCAT(npcinfo, ' - ', relation), IF(npcname != "", "1", "0"),npcname,1 FROM `mybb_rprelations`



Templates:

relas_ucp_nav - navigation anzeigen im UCP
relas_usercp - Anzeige der Verwaltung im UCP
relas_ucp_catbit - Kategorien verwalten im UCP
relas_ucp_cats - Kategorien der Benutzer im UCP
relas_anfragen - Charaktere die angefragt haben und bestätigt werden müssen (im UCP)
relas_ucp_offene_nr - Anzeige eigene Anfragen an andere UCP (Rahmen um erinnerung und zurücknehmen)
relas_ucp_offene_eigene -  Anzeige eigene Anfragen an andere UCP Erinnerung und zrücknehmen
relas_accepted - angenommene Charaktere anzeige und Verwaltung im UCP
relas_ucpAddNPC - NPCs im UCP hinzufügen
relas_memberprofil - Anzeige im Memberprofil
relas_showInProfil - Charaktere im Profil anzeigen 
relas_memberprofil_anfrage - Anfrage im Memberprofil


Variable fürs Profil:
{$relas_profil}







