<?php
/* $Id$ */
// vim: expandtab sw=4 ts=4 sts=4:


?>
<!-- Set on key handler for moving using by Ctrl+arrows -->
<script type="text/javascript" language="javascript">
<!--
document.onkeydown = onKeyDownArrowsHandler;
// -->
</script>

<form method="post" action="<?php echo $action; ?>">
<?php
echo PMA_generate_common_hidden_inputs($db, $table);
if ($action == 'tbl_create.php3') {
    ?>
    <input type="hidden" name="reload" value="1" />
    <?php
}
else if ($action == 'tbl_addfield.php3') {
    echo "\n";
    ?>
    <input type="hidden" name="after_field" value="<?php echo $after_field; ?>" />
    <?php
}
echo "\n";
$is_backup = ($action != 'tbl_create.php3' && $action != 'tbl_addfield.php3');
?>

    <table border="<?php echo $cfg['Border']; ?>">
    <tr>
        <th><?php echo $strField; ?></th>
        <th><?php echo $strType . '<br /><span style="font-weight: normal">' . PMA_showMySQLDocu('Reference', 'Column_types'); ?></span></th>
        <th><?php echo $strLengthSet; ?></th>
        <th><?php echo $strAttr; ?></th>
        <th><?php echo $strNull; ?></th>
        <th><?php echo $strDefault; ?>**</th>
        <th><?php echo $strExtra; ?></th>
<?php
require('./libraries/relation.lib.php3');
require('./libraries/transformations.lib.php3');
$cfgRelation = PMA_getRelationsParam();

$comments_map = array();

if ($cfgRelation['commwork']) {
    $comments_map = PMA_getComments($db, $table);
    echo '<th>' . $strComments . '</th>';
}

$comments_map = array();
$mime_map = array();
$available_mime = array();

if ($cfgRelation['commwork']) {
    $comments_map = PMA_getComments($db, $table);
    echo '<th>' . $strComments . '</th>';

    if ($cfg['BrowseMIME']) {
        $mime_map = PMA_getMIME($db, $table);
        $available_mime = PMA_getAvailableMIMEtypes();

        echo '<th>' . $strMIME_MIMEtype . '</th>';
        echo '<th>' . $strMIME_transformation . '</th>';
        echo '<th>' . $strMIME_transformation_options . '***</th>';
    }
}


// lem9: We could remove this 'if' and let the key information be shown and
// editable. However, for this to work, tbl_alter must be modified to use the
// key fields, as tbl_addfield does.

if (!$is_backup) {
    echo "        <th>$strPrimary</th>\n";
    echo "        <th>$strIndex</th>\n";
    echo "        <th>$strUnique</th>\n";
    echo "        <th>---</th>\n";
    echo "        <th>$strIdxFulltext</th>\n";
}
?>
    </tr>

<?php
for ($i = 0 ; $i < $num_fields; $i++) {
    if (isset($fields_meta)) {
        $row = $fields_meta[$i];
    }
    $bgcolor = ($i % 2) ? $cfg['BgcolorOne'] : $cfg['BgcolorTwo'];
    ?>
    <tr>
        <td bgcolor="<?php echo $bgcolor; ?>">
    <?php
    if ($is_backup) {
        echo "\n";
        ?>
            <input type="hidden" name="field_orig[]" value="<?php if (isset($row) && isset($row['Field'])) echo urlencode($row['Field']); ?>" />
        <?php
    }
    echo "\n";
    ?>
            <input id="field_<?php echo $i; ?>_1" type="text" name="field_name[]" size="10" maxlength="64" value="<?php if (isset($row) && isset($row['Field'])) echo str_replace('"', '&quot;', $row['Field']); ?>" class="textfield" />
        </td>
        <td bgcolor="<?php echo $bgcolor; ?>">
            <select name="field_type[]" id="field_<?php echo $i; ?>_2">
    <?php
    echo "\n";
    if (empty($row['Type'])) {
        $row['Type'] = '';
        $type        = '';
    }
    else if (get_magic_quotes_gpc()) {
        $type        = stripslashes($row['Type']);
    }
    else {
        $type        = $row['Type'];
    }
    // set or enum types: slashes single quotes inside options
    if (eregi('^(set|enum)\((.+)\)$', $type, $tmp)) {
        $type   = $tmp[1];
        $length = substr(ereg_replace('([^,])\'\'', '\\1\\\'', ',' . $tmp[2]), 1);
    } else {
        $type   = eregi_replace('BINARY', '', $type);
        $type   = eregi_replace('ZEROFILL', '', $type);
        $type   = eregi_replace('UNSIGNED', '', $type);

        $length = $type;
        $type   = chop(eregi_replace('\\(.*\\)', '', $type));
        if (!empty($type)) {
            $length = eregi_replace("^$type\(", '', $length);
            $length = eregi_replace('\)$', '', trim($length));
        }
        if ($length == $type) {
            $length = '';
        }
    } // end if else

    for ($j = 0; $j < count($cfg['ColumnTypes']); $j++) {
        echo '                <option value="'. $cfg['ColumnTypes'][$j] . '"';
        if (strtoupper($type) == strtoupper($cfg['ColumnTypes'][$j])) {
            echo ' selected="selected"';
        }
        echo '>' . $cfg['ColumnTypes'][$j] . '</option>' . "\n";
    } // end for
    ?>
            </select>
        </td>
        <td bgcolor="<?php echo $bgcolor; ?>">
    <?php
    if ($is_backup) {
        echo "\n";
        ?>
            <input type="hidden" name="field_length_orig[]" value="<?php echo urlencode($length); ?>" />
        <?php
    }
    echo "\n";
    ?>
            <input id="field_<?php echo $i; ?>_3" type="text" name="field_length[]" size="8" value="<?php echo str_replace('"', '&quot;', $length); ?>" class="textfield" />
        </td>
        <td bgcolor="<?php echo $bgcolor; ?>">
            <select name="field_attribute[]" id="field_<?php echo $i; ?>_4">
    <?php
    echo "\n";
    if (eregi('^(set|enum)$', $type)) {
        $binary           = 0;
        $unsigned         = 0;
        $zerofill         = 0;
    } else {
        $binary           = eregi('BINARY', $row['Type'], $test_attribute1);
        $unsigned         = eregi('UNSIGNED', $row['Type'], $test_attribute2);
        $zerofill         = eregi('ZEROFILL', $row['Type'], $test_attribute3);
    }
    $strAttribute     = '';
    if ($binary) {
        $strAttribute = 'BINARY';
    }
    if ($unsigned) {
        $strAttribute = 'UNSIGNED';
    }
    if ($zerofill) {
        $strAttribute = 'UNSIGNED ZEROFILL';
    }
    for ($j = 0;$j < count($cfg['AttributeTypes']); $j++) {
        echo '                <option value="'. $cfg['AttributeTypes'][$j] . '"';
        if (strtoupper($strAttribute) == strtoupper($cfg['AttributeTypes'][$j])) {
            echo ' selected="selected"';
        }
        echo '>' . $cfg['AttributeTypes'][$j] . '</option>' . "\n";
    }
    ?>
            </select>
        </td>
        <td bgcolor="<?php echo $bgcolor; ?>">
            <select name="field_null[]" id="field_<?php echo $i; ?>_5">
    <?php
    if (!isset($row) || empty($row['Null'])) {
        echo "\n";
        ?>
                <option value="NOT NULL">not null</option>
                <option value="">null</option>
        <?php
    } else {
        echo "\n";
        ?>
                <option value="">null</option>
                <option value="NOT NULL">not null</option>
        <?php
    }
    echo "\n";
    ?>
            </select>
        </td>
    <?php
    if (isset($row)
        && !isset($row['Default']) && !empty($row['Null'])) {
        $row['Default'] = 'NULL';
    }
    echo "\n";
    ?>
        <td bgcolor="<?php echo $bgcolor; ?>">
    <?php
    if ($is_backup) {
        echo "\n";
        ?>
            <input type="hidden" name="field_default_orig[]" size="8" value="<?php if(isset($row) && isset($row['Default'])) echo urlencode($row['Default']); ?>" />
        <?php
    }
    echo "\n";
    ?>
            <input id="field_<?php echo $i; ?>_6" type="text" name="field_default[]" size="8" value="<?php if(isset($row) && isset($row['Default'])) echo str_replace('"', '&quot;', $row['Default']); ?>" class="textfield" />
        </td>
        <td bgcolor="<?php echo $bgcolor; ?>">
            <select name="field_extra[]" id="field_<?php echo $i; ?>_7">
    <?php
    if(!isset($row) || empty($row['Extra'])) {
        echo "\n";
        ?>
                <option value=""></option>
                <option value="AUTO_INCREMENT">auto_increment</option>
        <?php
    } else {
        echo "\n";
        ?>
                <option value="AUTO_INCREMENT">auto_increment</option>
                <option value=""></option>
        <?php
    }
    echo "\n";
    ?>
            </select>
        </td>
    <?php
    // garvin: comments
    if ($cfgRelation['commwork']) {
    ?>
        <td bgcolor="<?php echo $bgcolor; ?>">
            <input id="field_<?php echo $i; ?>_7" type="text" name="field_comments[]" size="8" value="<?php echo (isset($row) && isset($row['Field']) && is_array($comments_map) && isset($comments_map[$row['Field']]) ?  htmlspecialchars($comments_map[$row['Field']]) : ''); ?>" class="textfield" />
        </td>
    <?php
    }

    // garvin: MIME-types
    if ($cfg['BrowseMIME'] && $cfgRelation['commwork']) {
    ?>
        <td bgcolor="<?php echo $bgcolor; ?>" valign="top">
            <select id="field_<?php echo $i; ?>_8" size="1" name="field_mimetype[]">
                <option value=""></option>
                <option value="auto">auto-detect</option>
            <?php
            if (is_array($available_mime['mimetype'])) {
                @reset($available_mime['mimetype']);
                while(list($mimekey, $mimetype) = each($available_mime['mimetype'])) {
                    $checked = (isset($row) && isset($row['Field']) && isset($mime_map[$row['Field']]['mimetype']) && ($mime_map[$row['Field']]['mimetype'] == str_replace('/', '_', $mimetype)) ? 'selected ' : '');
                    echo '<option value="' . str_replace('/', '_', $mimetype) . '" ' . $checked . '>' . htmlspecialchars($mimetype) . '</option>';
                }
            }
           ?>
           </select>
        </td>

        <td bgcolor="<?php echo $bgcolor; ?>" valign="top">
            <select id="field_<?php echo $i; ?>_9" size="1" name="field_transformation[]">
                <option value=""></option>
            <?php
            if (is_array($available_mime['transformation'])) {
                @reset($available_mime['transformation']);
                while(list($mimekey, $transform) = each($available_mime['transformation'])) {
                    $checked = (isset($row) && isset($row['Field']) && isset($mime_map[$row['Field']]['transformation']) && ($mime_map[$row['Field']]['transformation'] == $available_mime['transformation_file'][$mimekey]) ? 'selected ' : '');
                    echo '<option value="' . $available_mime['transformation_file'][$mimekey] . '" ' . $checked . '>' . htmlspecialchars($transform) . '</option>' . "\n";
                }
            }
           ?>
           </select>
        </td>

        <td bgcolor="<?php echo $bgcolor; ?>" valign="top">
            <input id="field_<?php echo $i; ?>_10" type="text" name="field_transformation_options[]" size="8" value="<?php echo (isset($row) && isset($row['Field']) && isset($mime_map[$row['Field']]['transformation_options']) ?  htmlspecialchars($mime_map[$row['Field']]['transformation_options']) : ''); ?>" class="textfield" />
        </td>
    <?php
    }

    // lem9: See my other comment about removing this 'if'.
    if (!$is_backup) {
        if (isset($row) && isset($row['Key']) && $row['Key'] == 'PRI') {
            $checked_primary = ' checked="checked"';
        } else {
            $checked_primary = '';
        }
        if (isset($row) && isset($row['Key']) && $row['Key'] == 'MUL') {
            $checked_index   = ' checked="checked"';
        } else {
            $checked_index   = '';
        }
        if (isset($row) && isset($row['Key']) && $row['Key'] == 'UNI') {
            $checked_unique   = ' checked="checked"';
        } else {
            $checked_unique   = '';
        }
        if (empty($checked_primary)
            && empty($checked_index)
            && empty($checked_unique)) {
            $checked_none = ' checked="checked"';
        }
        if (PMA_MYSQL_INT_VERSION >= 32323
            &&(isset($row) && isset($row['Comment']) && $row['Comment'] == 'FULLTEXT')) {
            $checked_fulltext = ' checked="checked"';
        } else {
            $checked_fulltext = '';
        }
        echo "\n";
        ?>
        <td align="center" bgcolor="<?php echo $bgcolor; ?>">
            <input type="radio" name="field_key_<?php echo $i; ?>" value="primary_<?php echo $i; ?>"<?php echo $checked_primary; ?> />
        </td>
        <td align="center" bgcolor="<?php echo $bgcolor; ?>">
            <input type="radio" name="field_key_<?php echo $i; ?>" value="index_<?php echo $i; ?>"<?php echo $checked_index; ?> />
        </td>
        <td align="center" bgcolor="<?php echo $bgcolor; ?>">
            <input type="radio" name="field_key_<?php echo $i; ?>" value="unique_<?php echo $i; ?>"<?php echo $checked_unique; ?> />
        </td>
        <td align="center" bgcolor="<?php echo $bgcolor; ?>">
            <input type="radio" name="field_key_<?php echo $i; ?>" value="none_<?php echo $i; ?>"<?php echo $checked_none; ?> />
        </td>
        <?php
        if (PMA_MYSQL_INT_VERSION >= 32323) {
            echo "\n";
            ?>
        <td bgcolor="<?php echo $bgcolor; ?>" nowrap="nowrap">
            <input type="checkbox" name="field_fulltext[]" value="<?php echo $i; ?>"<?php echo $checked_fulltext; ?> />
        </td>
            <?php
        } // end if (PMA_MYSQL_INT_VERSION >= 32323)
        echo "\n";
    } // end if ($action ==...)
    echo "\n";
    ?>
    </tr>
    <?php
    echo "\n";
} // end for
?>
    </table>
    <br />

<?php
if ($action == 'tbl_create.php3' && PMA_MYSQL_INT_VERSION >= 32300) {
    echo "\n";
    ?>
    <table>
    <tr valign="top">
        <td><?php echo $strTableComments; ?>&nbsp;:</td>
    <?php
    if ($action == 'tbl_create.php3') {
        echo "\n";
        ?>
        <td width="25">&nbsp;</td>
        <td><?php echo $strTableType; ?>&nbsp;:</td>
        <?php
    }
    echo "\n";
    ?>
    </tr>
    <tr>
        <td>
            <input type="text" name="comment" size="40" maxlength="80" class="textfield" />
        </td>
    <?php
    // BEGIN - Table Type - 2 May 2001 - Robbat2
    // change by staybyte - 11 June 2001
    if ($action == 'tbl_create.php3') {
        // find mysql capability - staybyte - 11. June 2001
        $query = 'SHOW VARIABLES LIKE \'have_%\'';
        $result = PMA_mysql_query($query);
        if ($result != FALSE && mysql_num_rows($result) > 0) {
            while ($tmp = PMA_mysql_fetch_array($result)) {
                if (isset($tmp['Variable_name'])) {
                    switch ($tmp['Variable_name']) {
                        case 'have_bdb':
                            if (isset($tmp['Variable_name']) && $tmp['Value'] == 'YES') {
                                $tbl_bdb    = TRUE;
                            }
                            break;
                        case 'have_gemini':
                            if (isset($tmp['Variable_name']) && $tmp['Value'] == 'YES') {
                                $tbl_gemini = TRUE;
                            }
                            break;
                        case 'have_innodb':
                            if (isset($tmp['Variable_name']) && $tmp['Value'] == 'YES') {
                                $tbl_innodb = TRUE;
                            }
                            break;
                        case 'have_isam':
                            if (isset($tmp['Variable_name']) && $tmp['Value'] == 'YES') {
                                $tbl_isam   = TRUE;
                            }
                            break;
                    } // end switch
                } // end if
            } // end while
        } // end if
        mysql_free_result($result);

        echo "\n";
        ?>
        <td width="25">&nbsp;</td>
        <td>
            <select name="tbl_type">
                <option value="Default"><?php echo $strDefault; ?></option>
                <option value="MYISAM">MyISAM</option>
                <option value="HEAP">Heap</option>
                <option value="MERGE">Merge</option>
                <?php if (isset($tbl_bdb)) { ?><option value="BDB">Berkeley DB</option><?php } ?>
                <?php if (isset($tbl_gemini)) { ?><option value="GEMINI">Gemini</option><?php } ?>
                <?php if (isset($tbl_innodb)) { ?><option value="InnoDB">INNO DB</option><?php } ?>
                <?php if (isset($tbl_isam)) { ?><option value="ISAM">ISAM</option><?php } ?>
            </select>
        </td>
        <?php
    }
    echo "\n";
    ?>
        </tr>
    </table>
    <br />
    <?php
}
echo "\n";
// END - Table Type - 2 May 2001 - Robbat2
?>

<input type="submit" name="submit" value="<?php echo $strSave; ?>" />
</form>

<table>
<tr>
    <td valign="top">*&nbsp;</td>
    <td>
        <?php echo $strSetEnumVal . "\n"; ?>
    </td>
</tr>
<tr>
    <td valign="top">**&nbsp;</td>
    <td>
        <?php echo $strDefaultValueHelp . "\n"; ?>
    </td>
</tr>

<?php
if ($cfgRelation['commwork'] && $cfg['BrowseMIME']) {
?>
<tr>
    <td valign="top" rowspan="2">***&nbsp;</td>
    <td>
        <?php echo $strMIME_transformation_options_note  . "\n"; ?>
    </td>
</tr>

<tr>
    <td>
        <?php echo sprintf($strMIME_transformation_note, '<a href="libraries/transformations/overview.php3?' . PMA_generate_common_url($db, $table) . '" target="_new">', '</a>') . "\n"; ?>
    </td>
</tr>
<?php
}
?>

</table>
<br />

<center><?php echo PMA_showMySQLDocu('Reference', 'CREATE_TABLE'); ?></center>
