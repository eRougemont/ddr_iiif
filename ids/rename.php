<?php declare(strict_types = 1);

// load listing
$listing = file(__DIR__ . '/listing.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
// from zotero Json, build: old id => new id
$idold_idnew = [];
$handle = fopen(__DIR__ . '/oldid_newid.tsv', "r");
while (($row = fgetcsv($handle, null, "\t")) !== FALSE) {
    $idold_idnew[$row[0]] = $row[1];
}
fclose($handle);
echo "actuel\tarchive\tiiif\n";
foreach($listing as $path) {
    $members = explode('/', $path);
    $ext = pathinfo(end($members), PATHINFO_EXTENSION);
    $name = pathinfo(end($members), PATHINFO_FILENAME);
    if (end($members) === '.DS_Store') {
        continue;
    }
    if ($members[1] == 'ddr-articles') {
        if (count($members) !== 4) {
            echo "$path\t### autre structure\n";
            continue;
        }
        $pos = strrpos($name, '_');
        if ($pos === false) {
            echo "$path\t### pas de page\n";
            continue;
        }
        $idold = substr($name, 0, $pos);
        if (!isset($idold_idnew[$idold])) {
            echo "$path\t### $idold non trouvé dans les URL\n";
            continue;
        }
        $page = str_pad(substr($name, $pos + 1), 3, '0', STR_PAD_LEFT);
        if (!$page || strlen($page) != 3) {
            echo "$path\t### $page, est-ce un no de page ?\n";
            continue;
        }
        $idnew = $idold_idnew[$idold];
        $collection = $members[2];
        // {id}/###.jpg
        echo "$path\tdepot/$collection/{$idnew}_{$page}.$ext\tiiif/ddr/{$idnew}/{$page}.$ext\n";
    }
    else if ($members[1] == 'ddr-livres') {
        if (count($members) !== 4) {
            echo "$path\t### autre structure\n";
            continue;
        }
        $pos = strrpos($name, '_');
        if ($pos === false) {
            echo "$path\t### pas de page\n";
            continue;
        }
        $idold = substr($name, 0, $pos);
        if (!isset($idold_idnew[$idold])) {
            echo "$path\t### $idold non trouvé dans les URL\n";
            continue;
        }
        $page = str_pad(substr($name, $pos + 1), 3, '0', STR_PAD_LEFT);
        if (!$page || strlen($page) != 3) {
            echo "$path\t### $page, est-ce un no de page ?\n";
            continue;
        }
        $idnew = $idold_idnew[$idold];
        echo "$path\tdepot/{$idnew}/{$idnew}_{$page}.$ext\tiiif/ddr/{$idnew}/{$page}.$ext\n";
    }
}