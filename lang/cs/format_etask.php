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
$string['studentprivacy'] = 'Soukromí studenta';
$string['studentprivacy_help'] = 'Toto nastavení určuje, zda může student v tabulce hodnocení vidět hodnocení ostatních nebo ne.';
$string['studentprivacy_no'] = 'Student může v tabulce hodnocení vidět hodnocení ostatních';
$string['studentprivacy_yes'] = 'Student může v tabulce hodnocení vidět pouze své hodnocení';
$string['gradeitemprogressbars'] = 'Přehled plnění hodnocené položky';
$string['gradeitemprogressbars_help'] = 'Toto nastavení určuje, zda se má studentovi zobrazit přehled plnění hodnocené položky v tabulce hodnocení.';
$string['gradeitemprogressbars_no'] = 'Skrýt studentovi přehled plnění hodnocené položky v tabulce hodnocení';
$string['gradeitemprogressbars_yes'] = 'Zobrazit studentovi přehled plnění hodnocené položky v tabulce hodnocení';
$string['studentsperpage'] = 'Počet studentů na stránce';
$string['studentsperpage_help'] = 'Toto nastavení určuje počet studentů na stránce v tabulce hodnocení.';
$string['gradeitemssorting'] = 'Řazení hodnocených položek';
$string['gradeitemssorting_help'] = 'Toto nastavení určuje, zda jsou hodnocené položky v tabulce hodnocení řazeny od nejnovějších, nejstarších nebo tak, jak jsou v kurzu.';
$string['gradeitemssorting_latest'] = 'Řadit hodnocené položky v tabulce hodnocení od nejnovějších';
$string['gradeitemssorting_oldest'] = 'Řadit hodnocené položky v tabulce hodnocení od nejstarších';
$string['gradeitemssorting_inherit'] = 'Řadit hodnocené položky v tabulce hodnocení tak, jak jsou v kurzu';
$string['placement'] = 'Umístění';
$string['placement_help'] = 'Toto nastavení určuje umístění tabulky hodnocení nad nebo pod tématy kurzu.';
$string['placement_above'] = 'Umístit tabulku hodnocení nad tématy kurzu';
$string['placement_below'] = 'Umístit tabulku hodnocení pod tématy kurzu';
// Plugin settings strings.
$string['registeredduedatemodules'] = 'Registrované moduly s datumem odevzdání';
$string['registeredduedatemodules_help'] = 'Určuje, v jakém databázovém poli modulu je ukládána hodnota datumu odevzání.';
// Legend strings.
$string['legend'] = 'Legenda';
$string['gradeitemcompleted'] = 'Dokončeno';
$string['gradeitempassed'] = 'Splněno';
$string['gradeitemfailed'] = 'Nesplněno';
// Popover strings.
$string['choose'] = 'Vyberte ...';
$string['showmore'] = 'Zobrazit více ...';
$string['timemodified'] = 'Poslední úprava {$a}';
$string['max'] = 'max.';
// Flash messages.
$string['gradepasschanged'] = 'Potřebné hodnocení u&nbsp;hodnocené položky <strong>{$a->itemname}</strong> bylo úspěšně změněno na <strong>{$a->gradepass}</strong>.';
$string['gradepassremoved'] = 'Potřebné hodnocení u&nbsp;hodnocené položky <strong>{$a}</strong> bylo úšpěšně odstraněno.';
$string['gradepassunablesave'] = 'Potřebné hodnocení u&nbsp;hodnocené položky <strong>{$a}</strong> nelze změnit. Prosím, zkuste to znovu později nebo kontaktujte vývojáře pluginu.';
