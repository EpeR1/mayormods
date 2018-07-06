<?php

    define('__PAGETITLE','Diák felmentése');
    define('__PAGEHELP','
A felmentésnek (mindhárom esetben igazgató által hozott határozat intézkedik a felmentésről!):
<ul>
<li style="padding-bottom:5px">1. adott tárgyból (rész)értékelés alól = órán részt vesz, érdemjegyet és osztályzatot szakvélemény alapján nem kap
<br/>	Példa: diszgráfia (magyar nyelv felmentés)
    <ul>
	<li>a) teljes tanév FM: értékelés alól; osztályzata/zárójegye FM.
	    "4/B. Mentesítve ... tantárgyból az értékelés és a minősítés alól N., Tl., B. "
	</li>
	<li>b) rész tanév (dátum intervallum): csupán egyedi záradékot kap.</li>
    </ul>

<li style="padding-bottom:5px">2. órák látogatása alól (tankör), igazgató által meghatározott időben, nevelőtestület által meghatározott módon ad számot tudásáról
<br/>	Példa: élsportoló (testnevelés)
<br/>   Teendő: [*] tankörben FM: óralátogatás alól és értékelés alól, de zárójegyet kap (!)
<br/>	Záradék: "10. A(z) ... tantárgy óráinak látogatása alól felmentve ... -tól ... -ig. N.
	Kiegészülhet: Osztályozó vizsgát köteles tenni."
</li>
<li style="padding-bottom:5px">3. tárgy tanulása alól = óralátogatás alól + évközi jegyet + zárójegyet sem kap (csak készségtárgyak!)
<br/>	Példa: testnevelés III.
<br/>	Teendő: * tárgy minden tanköréből kiléptetni, * félévi(ha félév előtti) és évvégi jegye FM.
<br/>	Záradék: "7. Mentesítve a(z) [a tantárgy neve] ... tantárgy tanulása alól. N., TI., B."
</li>
</ul>
');

    define('_FELMENTES_TARGY_TANULASA_ALOL','Felmentés tárgy tanulása alól');
    define('_FELMENTES_TARGY_ERTEKELES_ALOL','Felmentés tárgy értékelése alól');
    define('_FELMENTES_TANKOR_ORALATOGATASA_ALOL','Felmentés tankör óralátogatása alól');
    define('_ROGZITETT_ZARADEKOK','Már rögzített záradékok');
    define('_TIPUS3','Válassz ki egy tárgyat és egy érvényességi dátumot amikortól él a felmentés:
    <ol><li>ezen dátummal kiléptettjük a tárgy tanköreiből,</li>
    <li>jegyeit és hiányzásait a referencia dátumtól töröljük,</li>
    <li> a dátum után véget érő félévekre FM bejegyzést rögzítünk osztályzatként (zárójegyként)</li></ol>');
    
    define('_OVIKOTELES','Osztályozóvizsgára kötelezett');
    define('_TARGY','Tárgy');
    define('_TANKOR','Tankör');
    define('_TOL_DT','Dátum (tól)');
    define('_IG_DT','Dátum (ig)');
    define('_NAP','Nap');
    define('_ORA','Óra');
    define('_NAPORAOPT','Kizárólag a megadott nap/óra alól való felmentés [opcionális]');

    define('_ZARADEK','Záradék [részfelmentés esetében]:');
    define('_ZARADEK1','miatt mentesítve a(z) ');
    define('_ZARADEK2','értékelés(e) és a minősítés(e) alól.');

    define('_EGESZ_EVRE','egész évre');
    define('_RESZBEN','részben');

    define('_ROGZITETT_FELMENTESEK','Már rögzített felmentések');
    define('_TORLESKENYSZERITES','Töröljük a már beírt hiányzásokat (és oszt. vizsga esetén jegyeket is)?');
    define('_ZARADEKNELKUL','A záradék felvétele nélkül (csak speciális esetben)');

    define('_IKTATOSZAM','Iktatószám');
?>
