<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component eTask topics course format.
 *
 * @package   format_etask
 * @copyright 2020, Martin Drlik <martin.drlik@email.cz>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Topics format strings.
$string['addsections'] = 'Přidat témata';
$string['currentsection'] = 'Aktuální téma';
$string['editsection'] = 'Upravit téma';
$string['editsectionname'] = 'Upravit název tématu';
$string['deletesection'] = 'Odstranit téma';
$string['newsectionname'] = 'Nový název tématu {$a}';
$string['sectionname'] = 'Téma';
$string['pluginname'] = 'eTask tematické uspořádání';
$string['section0name'] = 'Úvod';
$string['page-course-view-topics'] = 'Hlavní stránka libovolného kurzu v eTask tématickém formátu';
$string['page-course-view-topics-x'] = 'Jakákoliv stránka kurzu s eTask tematickým uspořádáním';
$string['hidefromothers'] = 'Skrýt téma';
$string['showfromothers'] = 'Zobrazit téma';
$string['privacy:metadata'] = 'Modul Formát eTask tématické uspořádání neukládá žádné osobní údaje.';
// Course format settings strings.
$string['privateview'] = 'Soukromé zobrazení';
$string['privateview_help'] = 'Toto nastavení určuje, zda mohou všichni studenti vidět své známky navzájem v tabulce hodnocení eTask nebo ne.';
$string['privateview_no'] = 'Každý student může vidět známky ostatních studentů kurzu/skupiny';
$string['privateview_yes'] = 'Přihlášený student může vidět pouze své vlastní známky';
$string['progressbars'] = 'Progress bary';
$string['progressbars_help'] = 'Toto nastavení určuje, zda se počítají progress bary Dokončeno a Splněno v nápovědě aktivity tabulky hodnocení eTask nebo ne.';
$string['progressbars_donotshow'] = 'Nezobrazovat progress bary Dokončeno a Splněno';
$string['progressbars_show'] = 'Zobrazit progress bary Dokončeno a Splněno';
$string['studentsperpage'] = 'Počet studentů na stránce';
$string['studentsperpage_help'] = 'Toto nastavení určuje počet studentů na stránce v tabulce hodnocení eTask.';
$string['activitiessorting'] = 'Řazení aktivit';
$string['activitiessorting_help'] = 'Toto nastavení určuje, zda jsou aktivity hodnotící tabulky eTask seřazeny od nejnovějších, od nejstarších nebo tak, jak jsou v kurzu.';
$string['activitiessorting_latest'] = 'Řadit aktivity od nejnovějších';
$string['activitiessorting_oldest'] = 'Řadit aktivity od nejstarších';
$string['activitiessorting_inherit'] = 'Řadit aktivity tak, jak jsou v kurzu';
$string['placement'] = 'Umístění';
$string['placement_help'] = 'Toto nastavení určuje umístění tabulky hodnocení eTask nad nebo pod tématy kurzu.';
$string['placement_above'] = 'Umístit hodnotící tabulku nad témata kurzu';
$string['placement_below'] = 'Umístit hodnotící tabulku pod témata kurzu';
// Plugin settings strings.
$string['registeredduedatemodules'] = 'Registrované moduly s datumem odevzdání';
$string['registeredduedatemodules_help'] = 'Určuje, v jakém databázovém poli modulu je ukládána hodnota datuu odevzání.';
// Legend strings.
$string['legend'] = 'Legenda';
$string['activitycompleted'] = 'Dokončeno';
$string['activitypassed'] = 'Splněno';
$string['activityfailed'] = 'Nesplněno';
// Popover strings.
$string['timemodified'] = 'Poslední úprava {$a}';
// Flash messages.
$string['gradepasschanged'] = 'Potřebná známka u&nbsp;aktivity <strong>{$a->itemname}</strong> byla úspěšně změněna na <strong>{$a->gradepass}</strong>.';
$string['gradepassremoved'] = 'Potřebná známka u&nbsp;aktivity <strong>{$a}</strong> byla úšpěšně odstraněna.';
$string['gradepassunablesave'] = 'Potřebnou známku u&nbsp;aktivity <strong>{$a}</strong> nelze změnit. Prosím, zkuste to znovu později nebo kontaktujte vývojáře pluginu.';
// Permissions in course.
$string['etask:teacher'] = 'Plná správa';
$string['etask:noneditingteacher'] = 'Jen pro čtení (všechna data)';
$string['etask:student'] = 'Jen pro čtení (data v kontextu soukromého zobrazení)';
