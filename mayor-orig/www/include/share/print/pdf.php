<?php

    if(function_exists('exec')==false) {
	$_SESSION['alert'][] = 'info:php config error:A nyomtatás nem működik. Az exec függvényhívás kikapcsolva!';
    }

    function fileNameNormal($fileName) {
	$search = array('&','|','/',' ','\'','"',',','?','!','<','>'); // a . engedélyezett
	$replace = array('_','_','_','_','_','_','_','_','_','_','_','_');
	return str_replace($search,$replace,$fileName);
    }

    function pdfLaTeX($TeX, $fileName) { // Beszúrt képekkel problémája van a pdflatex-nek

	global $policy, $page, $sub, $f;

	$dir = _DOWNLOADDIR."/$policy/$page/$sub/$f";

	// könyvtár ellenőrzése
        $ftex = fopen($dir.'/'.$fileName.'.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);

        exec('HOME=/tmp && export HOME && cd '.$dir.' && pdflatex -output-directory '.$dir.' "'.$dir.'/'.$fileName.'.tex"');
	return true;

    }

    function pdfLaTeXTwice($TeX, $fileName) { // Pl. longtable miatt kétszer kell futtatni...

	global $policy, $page, $sub, $f;

	$dir = _DOWNLOADDIR."/$policy/$page/$sub/$f";

	// könyvtár ellenőrzése
        $ftex = fopen($dir.'/'.$fileName.'.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);

        exec('HOME=/tmp && export HOME && cd '.$dir.' && pdflatex -output-directory '.$dir.' "'.$dir.'/'.$fileName.'.tex"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && pdflatex -output-directory '.$dir.' "'.$dir.'/'.$fileName.'.tex"');
	return true;

    }

    function pdfLaTeXA4($TeX, $fileName) { // TeX --> DVI --> PS --> PDF - így jók a képek

	global $policy, $page, $sub, $f;

	$dir = _DOWNLOADDIR."/$policy/$page/$sub/$f";

	// könyvtár ellenőrzése
        $ftex = @fopen($dir.'/'.$fileName.'.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'-A4.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);
//        exec('cd '.$dir.' && pdflatex -output-directory '.$dir.' '.$dir.'/'.$fileName.'-A4.tex');

        exec('HOME=/tmp && export HOME && cd '.$dir.' && latex "'.$dir.'/'.$fileName.'.tex"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && dvips -R0 -t a4 "'.$dir.'/'.$fileName.'.dvi"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && ps2pdf "'.$dir.'/'.$fileName.'.ps"');

	return true;

    }

    function pdfLaTeXA4Split($TeX, $fileName) { // TeX --> DVI --> PS --> PDF --> pdftk --> zip - lapokra szétvágott

	global $policy, $page, $sub, $f;

	$dir = _DOWNLOADDIR."/$policy/$page/$sub/$f";

	// könyvtár ellenőrzése
        $ftex = @fopen($dir.'/'.$fileName.'.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'-A4.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);

        exec('HOME=/tmp && export HOME && cd '.$dir.' && latex "'.$dir.'/'.$fileName.'.tex"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && dvips -R0 -t a4 "'.$dir.'/'.$fileName.'.dvi"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && ps2pdf "'.$dir.'/'.$fileName.'.ps"');

	exec('HOME=/tmp && export HOME && cd '.$dir.' && mkdir -p '.$dir.'/'.$fileName);
	exec('HOME=/tmp && export HOME && cd '.$dir.'/'.$fileName.' && pdftk ../'.$fileName.'.pdf burst');
	exec('HOME=/tmp && export HOME && cd '.$dir.' && zip -r '.$fileName.'.zip '.$fileName );
	exec('HOME=/tmp && export HOME && cd '.$dir.' && rm -r '.$dir.'/'.$fileName);

	return true;

    }


    function pdfLaTeXA5($TeX, $fileName) {

	global $policy, $page, $sub, $f;

	$dir = _DOWNLOADDIR."/$policy/$page/$sub/$f";

	// könyvtár ellenőrzése
        $ftex = fopen($dir.'/'.$fileName.'-A5.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'-A5.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);
//        exec('cd '.$dir.' && pdflatex -output-directory '.$dir.' '.$dir.'/'.$fileName.'-A5.tex');

        exec('HOME=/tmp && export HOME && cd '.$dir.' && latex "'.$dir.'/'.$fileName.'-A5.tex"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && dvips -R0 -t a5 "'.$dir.'/'.$fileName.'-A5.dvi"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && ps2pdf "'.$dir.'/'.$fileName.'-A5.ps"');

        $TeX = '\documentclass[a4paper,landscape,10pt]{article}'."\n";
        $TeX .= '\usepackage[final]{pdfpages}'."\n";
        $TeX .= '\begin{document}'."\n";
        $TeX .= '\includepdf[nup=2x1, pages={-}]{'.$dir.'/'.$fileName.'-A5.pdf}'."\n";
        $TeX .= '\end{document}'."\n";

        $ftex = fopen($dir.'/'.$fileName.'.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);
	exec('HOME=/tmp && export HOME && cd '.$dir.' && rm "'.$dir.'/'.$fileName.'.aux"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && pdflatex -output-directory '.$dir.' "'.$dir.'/'.$fileName.'.tex"');

	return true;

    }

    function pdfLaTeXA5Booklet($TeX, $fileName) {

	global $policy, $page, $sub, $f;

	$dir = _DOWNLOADDIR."/$policy/$page/$sub/$f";

	// könyvtár ellenőrzése
        $ftex = fopen($dir.'/'.$fileName.'-A5.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'-A5.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);
//        exec('cd '.$dir.' && pdflatex -output-directory '.$dir.' '.$dir.'/'.$fileName.'-A5.tex');

        exec('HOME=/tmp && export HOME && cd '.$dir.' && latex "'.$dir.'/'.$fileName.'-A5.tex"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && dvips -R0 -t a5 "'.$dir.'/'.$fileName.'-A5.dvi"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && ps2pdf "'.$dir.'/'.$fileName.'-A5.ps"');

        $TeX = '\documentclass[a4paper,landscape,10pt]{article}'."\n";
        $TeX .= '\usepackage[final]{pdfpages}'."\n";
        $TeX .= '\begin{document}'."\n";
        $TeX .= '\includepdf[nup=2x1, pages={-}, signature*=4]{'.$dir.'/'.$fileName.'-A5.pdf}'."\n";
        $TeX .= '\end{document}'."\n";

        $ftex = fopen($dir.'/'.$fileName.'.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);
	exec('HOME=/tmp && export HOME && cd '.$dir.' && rm "'.$dir.'/'.$fileName.'.aux"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && pdflatex -output-directory '.$dir.' "'.$dir.'/'.$fileName.'.tex"');

	return true;

    }

    function pdfLaTeXA5Booklets($TeX, $fileName) {

	global $policy, $page, $sub, $f;

	define('__A5BOOKLETSCUTHERE','%%% cut here %%%');

	$dir = _DOWNLOADDIR."/$policy/$page/$sub/$f";

	// könyvtár ellenőrzése
        $ftex = fopen($dir.'/'.$fileName.'-A5.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'-A5.tex';
	    return false;
	}
	// A forrást szétvágjuk több darabba a "%%% cut here %%%" string mentén
	$T[] = explode(__A5BOOKLETSCUTHERE, $TeX);
// Itt tart a félkész fejlesztés... :)
        fputs($ftex, $TeX);
        fclose($ftex);
//        exec('cd '.$dir.' && pdflatex -output-directory '.$dir.' '.$dir.'/'.$fileName.'-A5.tex');

        exec('HOME=/tmp && export HOME && cd '.$dir.' && latex "'.$dir.'/'.$fileName.'-A5.tex"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && dvips -R0 -t a5 "'.$dir.'/'.$fileName.'-A5.dvi"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && ps2pdf "'.$dir.'/'.$fileName.'-A5.ps"');

        $TeX = '\documentclass[a4paper,landscape,10pt]{article}'."\n";
        $TeX .= '\usepackage[final]{pdfpages}'."\n";
        $TeX .= '\begin{document}'."\n";
        $TeX .= '\includepdf[nup=2x1, pages={-}, signature*=4]{'.$dir.'/'.$fileName.'-A5.pdf}'."\n";
        $TeX .= '\end{document}'."\n";

        $ftex = fopen($dir.'/'.$fileName.'.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);
	exec('HOME=/tmp && export HOME && cd '.$dir.' && rm "'.$dir.'/'.$fileName.'.aux"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && pdflatex -output-directory '.$dir.' "'.$dir.'/'.$fileName.'.tex"');

	return true;

    }



    function pdfLaTeXA6($TeX, $fileName) {

	global $policy, $page, $sub, $f;

	$dir = _DOWNLOADDIR."/$policy/$page/$sub/$f";

	// könyvtár ellenőrzése
        $ftex = fopen($dir.'/'.$fileName.'-A6.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'-A6.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);
        exec('HOME=/tmp && export HOME && cd '.$dir.' && pdflatex -output-directory '.$dir.' "'.$dir.'/'.$fileName.'-A6.tex"');

        $TeX = '\documentclass[a4paper,10pt]{article}'."\n";
        $TeX .= '\usepackage[final]{pdfpages}'."\n";
        $TeX .= '\begin{document}'."\n";
        $TeX .= '\includepdf[nup=2x2, pages={-}]{'.$dir.'/'.$fileName.'-A6.pdf}'."\n";
        $TeX .= '\end{document}'."\n";

        $ftex = fopen($dir.'/'.$fileName.'.tex', 'w');
        if (!$ftex) {
	    $_SESSION['alert'][] = 'message:file_write_failure:'.$dir.'/'.$fileName.'.tex';
	    return false;
	}
        fputs($ftex, $TeX);
        fclose($ftex);
	exec('HOME=/tmp && export HOME && cd '.$dir.' && rm "'.$dir.'/'.$fileName.'.aux"');
        exec('HOME=/tmp && export HOME && cd '.$dir.' && pdflatex -output-directory '.$dir.' "'.$dir.'/'.$fileName.'.tex"');

	return true;

    }

?>
