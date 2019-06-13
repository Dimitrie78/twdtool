<?php
include "verify.php";
$showfilter = 0;
$selng      = '';
$seldn      = '';
$nid        = '';
if (!empty($_GET['nid']))
{
    $cbnid = preg_replace('/\D/', '', explode('-',$_GET['nid'])[0]);
    $cbgid = explode('-',$_GET['nid'])[1];
}
if ($_SESSION['gid'] > 0)
{
    $segid = $_SESSION['gid'];
}
else
{
    $segid = '0';
}
if (!isset($_GET['do']))
{
    if (isset($_GET['msg']))
    {
        msgbox($_GET['msg']);
    }
?>
Zeilenschaltungen werden automatisch registriert. Spezielle Formatierungen müssen in HTML-Code erfasst werden.<br>
    <?php
    if (!isdev())
    {
        $filter = 'WHERE gid = ' . $segid . '  AND active = 1 AND devnews = 0';
    }
    if (isdev())
    {
        if (!empty($cbnid))
        {
            $filter = 'WHERE active = 1 AND id = ' . $cbnid;
        }
        else
        {
            if ($_POST['sgid'])
            {
                if ($_POST['sgid'] == "dn")
                {
                    $filter = 'WHERE active = 1 AND devnews = 1';
                }
                elseif (ctype_digit($_POST['sgid']))
                {
                    $filter = 'WHERE gid = ' . $_POST['sgid'] . '  AND active = 1 AND devnews = 0';
                }
            }
            else
            {
                $filter = 'WHERE active = 1 AND devnews = 1';
            }
        }
    }
    if (!empty($cbgid))
    {
        if ($cbgid == 'ng')
        {
            $selng = 'selected';
        }
        elseif ($cbgid == 'dn')
        {
            $seldn = 'selected';
        }
    }
    if (isset($_POST['sgid']))
    {
        if ($_POST['sgid'] == "ng")
        {
            $filter = 'WHERE active = 1 AND devnews = 0 and gid  = 0';
        }
        if ($_POST['sgid'] == 'ng')
        {
            $selng = 'selected';
        }
        elseif ($_POST['sgid'] == 'dn')
        {
            $seldn = 'selected';
        }
    }
    if ($showfilter === 1)
    {
        echo '<br><b>Filter: ' . $filter . '</b><br><br>';
    }
    if (isdev())
    {
?>
   <form class="form-vertical" role="form" id = "selgrp" method = "POST" action = "?action=frontpageedit" >
    <div class="form-group" style="width:auto;">
    <label for="inputGroup" class = "control-label">Gruppe wählen: <span class="fas fa-arrow-right"></span></label>
      <select onchange="document.getElementById('selgrp').submit()" id="inputGroup" name = "sgid" class = "form-control" style="width:auto;min-width:200px;">
      <option value="dn" <?php echo $seldn;?>>DEV-News (Gepinnt)</option>
      <option value="ng" <?php echo $selng;?>>Gruppe</option>
    <?php
        $sql = 'SELECT id, tag, name FROM `' . $config->db_pre . 'groups` ORDER BY name ASC';
        foreach ($pdo->query($sql) as $row)
        {
            if ($_POST['sgid'] == $row['id'] || $cbgid == $row['id'])
            {
                $gidselected = ' selected';
            }
            echo '<option value="' . $row['id'] . '" ' . $gidselected . '>[' . $row['tag'] . '] ' . $row['name'] . '</option>';
            $gidselected = '';
        }
?>
     </select>
    </div>
    </form>
    
    <?php
    }
    $news    = $pdo->query("SELECT `id`, `text`,`devnews`, DATE_FORMAT(`ndate`, '%d.%m.%Y %H:%i:%s') as ndate
                         FROM " . $config->db_pre . "news " . $filter . "")->fetch();
    $lupdate = (isset($news['ndate'])) ? $news['ndate'] : 'Noch keines';
?> Letztes Update: <?php echo $lupdate;?>
    <form action="?action=frontpageedit&do=update" method = "POST" autocomplete="no">
      <input type = "hidden" name = "editid" type="text" value = "<?php echo $news['id'];?>">
    <?php
    if (isdev())
    {
?>
      <input type = "hidden" name = "dnews" type="text" value = "<?php echo $news['devnews'];?>">
      <input type = "hidden" name = "wgid" type="text" value = "<?php echo (!empty($cbgid) ? $cbgid : $_POST['sgid']);?>">
	<?php
    }
?>
    <div class="form-group">
      <textarea class="form-control" id="fpd" rows="15" name = "text"><?php echo br2nl($news['text']);?></textarea>
    </div>
    <button type="submit" name = "updatenews" class="btn btn-success">Update</button>
    </form>
	<?php
}
if (isset($_GET['do']) && $_GET['do'] == "update" && isset($_POST['updatenews']))
{
    $editid = preg_replace('/\D/', '', $_POST['editid']);
    if (empty($editid))
    {
        $date = date('Y-m-d H:i:s');
        if (isdev())
        {
            if ($_POST['wgid'] == 'dn')
            {
                echo 'Trage neue DEV-News ein...';
                $statement = $pdo->prepare("INSERT INTO " . $config->db_pre . "news(gid, ndate, text, active, devnews)
                                            VALUES(:gid, :ndate, :text, :active, :devnews)");
                $statement->execute(array(
                    "gid" => '0',
                    "ndate" => $date,
                    "text" => $_POST['text'],
                    "active" => 1,
                    "devnews" => 1
                ));
                $lid = $pdo->lastInsertId() . '-dn';
            }
            elseif ($_POST['wgid'] == 'ng')
            {
                //News für Gruppenlose
                $statement = $pdo->prepare("INSERT INTO " . $config->db_pre . "news(gid, ndate, text, active, devnews)
                                            VALUES(:gid, :ndate, :text, :active, :devnews)");
                $statement->execute(array(
                    "gid" => '0',
                    "ndate" => $date,
                    "text" => $_POST['text'],
                    "active" => 1,
                    "devnews" => 0
                ));
                $lid = $pdo->lastInsertId() . '-ng';
            }
            elseif (ctype_digit($_POST['wgid']))
            {
                //Trage neue News für Gruppen-ID-Post ein
                $statement = $pdo->prepare("INSERT INTO " . $config->db_pre . "news(gid, ndate, text, active, devnews)
                                            VALUES(:gid, :ndate, :text, :active, :devnews)");
                $statement->execute(array(
                    "gid" => $_POST['wgid'],
                    "ndate" => $date,
                    "text" => $_POST['text'],
                    "active" => 1,
                    "devnews" => 0
                ));
                $lid = $pdo->lastInsertId();
            }
            $nid = '&nid=' . $lid . '-' . $_POST['wgid'];
        }
        else
        {
            //Trage neue News für die aktuell eingeloggte Session-GID ein
            $statement = $pdo->prepare("INSERT INTO " . $config->db_pre . "news(gid, ndate, text, active, devnews)
                                            VALUES(:gid, :ndate, :text, :active, :devnews)");
            $statement->execute(array(
                "gid" => $segid,
                "ndate" => $date,
                "text" => $_POST['text'],
                "active" => 1,
                "devnews" => 0
            ));
        }
        $count = $statement->rowCount();
        if ($count == '1')
        {
            header('Location: ?action=frontpageedit&msg=addsuccess' . $nid);
        }
        else
        {
            header('Location: ?action=frontpageedit&msg=addfail' . $nid);
        }
    }
    else
    {
        //Editid nicht leer UPDATE
        if (isdev())
        {
            $nid = '&nid=' . $editid . '-' . $_POST['wgid'];
        }
        $query = $pdo->prepare('UPDATE ' . $config->db_pre . 'news
        SET text = :text, ndate = NOW()
        WHERE id = ' . $editid . '');
        if ($query->execute(array(
            ':text' => $_POST['text']
        )))
        {
            header('Location: ?action=frontpageedit&msg=updatesuccess' . $nid);
        }
        else
        {
            header('Location: ?action=frontpageedit&msg=updatefail' . $nid);
        }
    }
}
?>