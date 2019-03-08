<?php

    function pdfBoritek($file, $ADAT) {

	$TeX .= '
% envelope.tex
\documentclass{letter}'."\n";

        $TeX .= '\usepackage[utf8x]{inputenc} % UTF-8 kódolású forrás (ucs)'."\n";
        $TeX .= '\usepackage{ucs} % Jobb UTF-8 támogatás'."\n";
        $TeX .= '\usepackage{t1enc}'."\n";
        $TeX .= '\usepackage[magyar]{babel} % magyar elválasztási szabályok'."\n";
        $TeX .= '\frenchspacing % a magyar tipográfiai szabályoknak megfelelő szóközök írásjelek után'."\n";

$TeX .= '\usepackage[margin=0.15in,papersize={11.4cm,16.2cm},landscape,twoside=false]{geometry}
\setlength\parskip{0pt}
\pagestyle{empty}
 
\begin{document}
'."\n";

 
	for ($i=0; $i<count($ADAT); $i++) {
	    $D = $ADAT[$i];
	    $TeX .= '\setlength\parindent{0pt}'."\n\n";
	    $TeX .= 'Városmajori Gimnázium és Kós Károly Általános Iskola'."\n\n";
	    $TeX .= '1122 Budapest,'."\n\n";
	    $TeX .= 'Városmajor u. 71.'."\n\n";
	    $TeX .= '\vspace{1.6in}'."\n\n";
#	    $TeX .= '\setlength\parindent{0.8in}'."\n\n";
	    $TeX .= '{\begin{center}\large '."\n";
	    $TeX .= $D['nev']." \end{center}}\n\n";
	    $TeX .= '\vspace{1cm}'."\n\n";
	    $TeX .= '\setlength\parindent{3.6in}'."\n\n";
	    $TeX .= $D['telepules']."\n\n";
	    $TeX .= $D['cim']."\n\n";
	    $TeX .= '\vspace{0.5cm}'."\n\n";
	    $TeX .= $D['irsz']."\n\n";
	    $TeX .= '\vspace{1cm}'."\n\n";
	    $TeX .= '\setlength\parindent{0pt}'."\n\n";
	    $TeX .= '{\tiny '."MaYoR/".$D['omkod']."}\n";
    	    $TeX .= '\newpage '."\n\n";
	}
	$TeX .= '\end{document}';

      return pdfLaTeX($TeX, $file);

    }

?>
