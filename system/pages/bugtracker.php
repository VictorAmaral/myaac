<?php
/**
 * Bug tracker
 *
 * @package   MyAAC
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.0.5
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Bug tracker';

if($logged)
{
    // type (1 = question; 2 = answer)
    // status (1 = open; 2 = new message; 3 = closed;)
    
    $dark = $config['darkborder'];
    $light = $config['lightborder'];
    
    $tags = array(1 => "[MAP]", "[WEBSITE]", "[CLIENT]", "[MONSTER]", "[NPC]", "[OTHER]");
        
    if(admin() and $_REQUEST['control'] == "true")
    {
        if(empty($_REQUEST['id']) and empty($_REQUEST['acc']) or !is_numeric($_REQUEST['acc']) or !is_numeric($_REQUEST['id']) )
            $bug[1] = $db->query('SELECT * FROM '.$db->tableName(TABLE_PREFIX . 'bugtracker').' where `type` = 1 order by `uid` desc');
        
        if(!empty($_REQUEST['id']) and is_numeric($_REQUEST['id']) and !empty($_REQUEST['acc']) and is_numeric($_REQUEST['acc']))
            $bug[2] = $db->query('SELECT * FROM '.$db->tableName(TABLE_PREFIX . 'bugtracker').' where `account` = '.$_REQUEST['acc'].' and `id` = '.$_REQUEST['id'].' and `type` = 1')->fetch();
        
        if(!empty($_REQUEST['id']) and is_numeric($_REQUEST['id']) and !empty($_REQUEST['acc']) and is_numeric($_REQUEST['acc']))
        {
            if(!empty($_REQUEST['reply']))
                $reply=true;
                
            $account = $ots->createObject('Account');
            $account->load($_REQUEST['acc']);
            $account->isLoaded();
            $players = $account->getPlayersList();
            
            if(!$reply)
            {
                if($bug[2]['status'] == 2)
                    $value = "<font color=green>[OPEN]</font>";
                elseif($bug[2]['status'] == 3)
                    $value = "<font color=red>[CLOSED]</font>";
                elseif($bug[2]['status'] == 1)
                    $value = "<font color=blue>[NEW ANSWER]</font>";
                    
                echo '<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=4 WIDTH=100%><TR BGCOLOR='.$config['vdarkborder'].'><TD COLSPAN=2 CLASS=white><B>Bug Tracker</B></TD></TR>';                            
                echo '<TR BGCOLOR="'.$dark.'"><td width=40%><i><b>Subject</b></i></td><td>'.$tags[$bug[2]['tag']].' '.$bug[2]['subject'].' '.$value.'</td></tr>';    
                echo '<TR BGCOLOR="'.$light.'"><td><i><b>Posted by</b></i></td><td>';    
                
                foreach($players as $player)
                {
                    echo ''.$player->getName().'<br>';
                }
                
                echo '</td></tr>';
                echo '<TR BGCOLOR="'.$dark.'"><td colspan=2><i><b>Description</b></i></td></tr>';    
                echo '<TR BGCOLOR="'.$light.'"><td colspan=2>'.nl2br($bug[2]['text']).'</td></tr>';    
                echo '</TABLE>';
                
                $answers = $db->query('SELECT * FROM '.$db->tableName(TABLE_PREFIX . 'bugtracker').' where `account` = '.$_REQUEST['acc'].' and `id` = '.$_REQUEST['id'].' and `type` = 2 order by `reply`');
                foreach($answers as $answer)
                {
                    if($answer['who'] == 1)
                        $who = "<font color=red>[ADMIN]</font>";
                    else
                        $who = "<font color=green>[PLAYER]</font>";
                        
                    echo '<br><TABLE BORDER=0 CELLSPACING=1 CELLPADDING=4 WIDTH=100%><TR BGCOLOR='.$config['vdarkborder'].'><TD COLSPAN=2 CLASS=white><B>Answer #'.$answer['reply'].'</B></TD></TR>';                            
                    echo '<TR BGCOLOR="'.$dark.'"><td width=70%><i><b>Posted by</b></i></td><td>'.$who.'</td></tr>';    
                    echo '<TR BGCOLOR="'.$light.'"><td colspan=2><i><b>Description</b></i></td></tr>';    
                    echo '<TR BGCOLOR="'.$dark.'"><td colspan=2>'.nl2br($answer['text']).'</td></tr>';    
                    echo '</TABLE>';
                }
                if($bug[2]['status'] != 3)
                    echo '<br><a href="index.php?subtopic=bugtracker&control=true&id='.$_REQUEST['id'].'&acc='.$_REQUEST['acc'].'&reply=true"><b>[REPLY]</b></a>';
            }
            else
            {
                if($bug[2]['status'] != 3)
                {
                    $reply = $db->query('SELECT MAX(reply) FROM `' . TABLE_PREFIX . 'bugtracker` where `account` = '.$_REQUEST['acc'].' and `id` = '.$_REQUEST['id'].' and `type` = 2')->fetch();
                    $reply = $reply[0] + 1;
                    $iswho = $db->query('SELECT * FROM `' . TABLE_PREFIX . 'bugtracker` where `account` = '.$_REQUEST['acc'].' and `id` = '.$_REQUEST['id'].' and `type` = 2 order by `reply` desc limit 1')->fetch();

                    if(isset($_POST['finish']))
                    {
                        if(empty($_POST['text']))
                            $error[] = "<font color=black><b>Description cannot be empty.</b></font>";
                        if($iswho['who'] == 1)
                            $error[] = "<font color=black><b>You must wait for User answer.</b></font>";
                        if(empty($_POST['status']))
                            $error[] = "<font color=black><b>Status cannot be empty.</b></font>";
                            
                        if(!empty($error))
                        {
                            foreach($error as $errors)
                                echo ''.$errors.'<br>';
                        }
                        else
                        {
                            $type = 2;
                            $INSERT = $db->query('INSERT INTO `' . TABLE_PREFIX . 'bugtracker` (`account`,`id`,`text`,`reply`,`type`, `who`) VALUES ('.$db->quote($_REQUEST['acc']).','.$db->quote($_REQUEST['id']).','.$db->quote($_POST['text']).','.$db->quote($reply).','.$db->quote($type).','.$db->quote(1).')');
                            $UPDATE = $db->query('UPDATE `' . TABLE_PREFIX . 'bugtracker` SET `status` = '.$_POST['status'].' where `account` = '.$_REQUEST['acc'].' and `id` = '.$_REQUEST['id'].'');
                            header('Location: index.php?subtopic=bugtracker&control=true&id='.$_REQUEST['id'].'&acc='.$_REQUEST['acc'].'');
                        }
                    }
                    echo '<br><form method="post" action=""><table><tr><td><i>Description</i></td><td><textarea name="text" rows="15" cols="35"></textarea></td></tr><tr><td>Status[OPEN]</td><td><input type=radio name=status value=2></td></tr><tr><td>Status[CLOSED]</td><td><input type=radio name=status value=3></td></tr></table><br><input type="submit" name="finish" value="Submit" class="input2"/></form>';
                }
                else
                {
                    echo "<br><font color=black><b>You can't add answer to closed bug thread.</b></font>";
                }
            }
            
            $post=true;
        }
        if(!$post)
        {
            echo '<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=4 WIDTH=100%><TR BGCOLOR='.$config['vdarkborder'].'><TD colspan=2 CLASS=white><B>Bug Tracker Admin</B></TD></TR>';            
            $i=1;
            foreach($bug[1] as $report)
            {
                if($report['status'] == 2)
                    $value = "<font color=green>[OPEN]</font>";
                elseif($report['status'] == 3)
                    $value = "<font color=red>[CLOSED]</font>";
                elseif($report['status'] == 1)
                    $value = "<font color=blue>[NEW ANSWER]</font>";
                            
                echo '<TR BGCOLOR="' . getStyle($i) . '"><td width=75%><a href="index.php?subtopic=bugtracker&control=true&id='.$report['id'].'&acc='.$report['account'].'">'.$tags[$report['tag']].' '.$report['subject'].'</a></td><td>'.$value.'</td></tr>';            
                        
                $showed=true;
                $i++;
            }
            echo '</TABLE>';
        }
    }
    else
    {        
        $acc = $account_logged->getId();
        $account_players = $account_logged->getPlayersList();
        
        foreach($account_players as $player)
        {
            $allow=true;
        }
        
        if(!empty($_REQUEST['id']))
            $id = addslashes(htmlspecialchars(trim($_REQUEST['id'])));
        
        if(empty($_REQUEST['id']))
            $bug[1] = $db->query('SELECT * FROM '.$db->tableName(TABLE_PREFIX . 'bugtracker').' where `account` = '.$account_logged->getId().' and `type` = 1 order by `id` desc');
        
        if(!empty($_REQUEST['id']) and is_numeric($_REQUEST['id']))
            $bug[2] = $db->query('SELECT * FROM '.$db->tableName(TABLE_PREFIX . 'bugtracker').' where `account` = '.$account_logged->getId().' and `id` = '.$id.' and `type` = 1')->fetch();
        else
            $bug[2] = NULL;
            
        if(!empty($_REQUEST['id']) and $bug[2] != NULL)
        {
            if(!empty($_REQUEST['reply']))
                $reply=true;
            
            if(!$reply)
            {
                if($bug[2]['status'] == 1)
                    $value = "<font color=green>[OPEN]</font>";
                elseif($bug[2]['status'] == 2)
                    $value = "<font color=blue>[NEW ANSWER]</font>";
                elseif($bug[2]['status'] == 3)
                    $value = "<font color=red>[CLOSED]</font>";
                    
                echo '<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=4 WIDTH=100%><TR BGCOLOR='.$config['vdarkborder'].'><TD COLSPAN=2 CLASS=white><B>Bug Tracker</B></TD></TR>';                            
                echo '<TR BGCOLOR="'.$dark.'"><td width=40%><i><b>Subject</b></i></td><td>'.$tags[$bug[2]['tag']].' '.$bug[2]['subject'].' '.$value.'</td></tr>';    
                echo '<TR BGCOLOR="'.$light.'"><td colspan=2><i><b>Description</b></i></td></tr>';    
                echo '<TR BGCOLOR="'.$dark.'"><td colspan=2>'.nl2br($bug[2]['text']).'</td></tr>';    
                echo '</TABLE>';
                
                $answers = $db->query('SELECT * FROM '.$db->tableName('wodzaac_bugtracker').' where `account` = '.$account_logged->getId().' and `id` = '.$id.' and `type` = 2 order by `reply`');
                foreach($answers as $answer)
                {
                    if($answer['who'] == 1)
                        $who = "<font color=red>[ADMIN]</font>";
                    else
                        $who = "<font color=green>[YOU]</font>";
                        
                    echo '<br><TABLE BORDER=0 CELLSPACING=1 CELLPADDING=4 WIDTH=100%><TR BGCOLOR='.$config['vdarkborder'].'><TD COLSPAN=2 CLASS=white><B>Answer #'.$answer['reply'].'</B></TD></TR>';                            
                    echo '<TR BGCOLOR="'.$dark.'"><td width=70%><i><b>Posted by</b></i></td><td>'.$who.'</td></tr>';    
                    echo '<TR BGCOLOR="'.$light.'"><td colspan=2><i><b>Description</b></i></td></tr>';    
                    echo '<TR BGCOLOR="'.$dark.'"><td colspan=2>'.nl2br($answer['text']).'</td></tr>';    
                    echo '</TABLE>';
                }
                if($bug[2]['status'] != 3)
                    echo '<br><a href="index.php?subtopic=bugtracker&id='.$id.'&reply=true"><b>[REPLY]</b></a>';
            }
            else
            {
                if($bug[2]['status'] != 3)
                {
                    $reply = $db->query('SELECT MAX(reply) FROM `' . TABLE_PREFIX . 'bugtracker` where `account` = '.$acc.' and `id` = '.$id.' and `type` = 2')->fetch();
                    $reply = $reply[0] + 1;
                    $iswho = $db->query('SELECT * FROM `wodzaac_bugtracker` where `account` = '.$acc.' and `id` = '.$id.' and `type` = 2 order by `reply` desc limit 1')->fetch();

                    if(isset($_POST['finish']))
                    {
                        if(empty($_POST['text']))
                            $error[] = "<font color=black><b>Description cannot be empty.</b></font>";
                        if($iswho['who'] == 0)
                            $error[] = "<font color=black><b>You must wait for Administrator answer.</b></font>";
                        if(!$allow)
                            $error[] = "<font color=black><b>You haven't any characters on account.</b></font>";
                            
                        if(!empty($error))
                        {
                            foreach($error as $errors)
                                echo ''.$errors.'<br>';
                        }
                        else
                        {
                            $type = 2;
                            $INSERT = $db->query('INSERT INTO `wodzaac_bugtracker` (`account`,`id`,`text`,`reply`,`type`) VALUES ('.$db->quote($acc).','.$db->quote($id).','.$db->quote($_POST['text']).','.$db->quote($reply).','.$db->quote($type).')');
                            $UPDATE = $db->query('UPDATE `wodzaac_bugtracker` SET `status` = 1 where `account` = '.$acc.' and `id` = '.$id.'');
                            header('Location: index.php?subtopic=bugtracker&id='.$id.'');
                        }
                    }
                    echo '<br><form method="post" action=""><table><tr><td><i>Description</i></td><td><textarea name="text" rows="15" cols="35"></textarea></td></tr></table><br><input type="submit" name="finish" value="Submit" class="input2"/></form>';
                }
                else
                {
                    echo "<br><font color=black><b>You can't add answer to closed bug thread.</b></font>";
                }
            }
            
            $post=true;
        }
        elseif(!empty($_REQUEST['id']) and $bug[2] == NULL)
        {
            echo '<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=4 WIDTH=100%><TR BGCOLOR='.$config['vdarkborder'].'><TD CLASS=white><B>Bug Tracker</B></TD></TR>';                            
            echo '<TR BGCOLOR="'.$dark.'"><td><i>Bug doesn\'t exist.</i></td></tr>';    
            echo '</TABLE>';
            $post=true;
        }
        
        if(!$post)
        {
            if($_REQUEST['add'] != TRUE)
            {
                echo '<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=4 WIDTH=100%><TR BGCOLOR='.$config['vdarkborder'].'><TD colspan=2 CLASS=white><B>Bug Tracker</B></TD></TR>';            
                foreach($bug[1] as $report)
                {
                    if($report['status'] == 1)
                        $value = "<font color=green>[OPEN]</font>";
                    elseif($report['status'] == 2)
                        $value = "<font color=blue>[NEW ANSWER]</font>";
                    elseif($report['status'] == 3)
                        $value = "<font color=red>[CLOSED]</font>";
                        
                    if(is_int($report['id'] / 2))
                    {
                        $bgcolor = $dark;
                    }
                    else
                    {
                        $bgcolor = $light;
                    }

                    echo '<TR BGCOLOR="'.$bgcolor.'"><td width=75%><a href="index.php?subtopic=bugtracker&id='.$report['id'].'">'.$tags[$report['tag']].' '.$report['subject'].'</a></td><td>'.$value.'</td></tr>';            
                    
                    $showed=true;
                }
                
                if(!$showed)
                {
                    echo '<TR BGCOLOR="'.$dark.'"><td><i>You don\'t have reported any bugs.</i></td></tr>';    
                }
                echo '</TABLE>';
                
                echo '<br><a href="index.php?subtopic=bugtracker&add=true"><b>[ADD REPORT]</b></a>';
            }
            elseif($_REQUEST['add'] == TRUE)
            {
                $thread = $db->query('SELECT * FROM `' TABLE_PREFIX . 'bugtracker` where `account` = '.$acc.' and `type` = 1 order by `id` desc')->fetch();
                $id_next = $db->query('SELECT MAX(id) FROM `' . TABLE_PREFIX . 'bugtracker` where `account` = '.$acc.' and `type` = 1')->fetch();
                $id_next = $id_next[0] + 1;
                
                if(empty($thread))
                    $thread['status'] = 3;
                    
                if(isset($_POST['submit']))
                {
                    if($thread['status'] != 3)
                        $error[] = "<font color=black><b>Can be only 1 open bug thread.</b></font>";
                    if(empty($_POST['subject']))
                        $error[] = "<font color=black><b>Subject cannot be empty.</b></font>";
                    if(empty($_POST['text']))
                        $error[] = "<font color=black><b>Description cannot be empty.</b></font>";
                    if(!$allow)
                        $error[] = "<font color=black><b>You haven't any characters on account.</b></font>";
                    if(empty($_POST['tags']))
                        $error[] = "<font color=black><b>Tag cannot be empty.</b></font>";
                        
                    if(!empty($error))
                    {
                        foreach($error as $errors)
                            echo ''.$errors.'<br>';
                    }
                    else
                    {
                        $type = 1;
                        $status = 1;
                        $INSERT = $db->query('INSERT INTO `' . TABLE_PREFIX . 'bugtracker` (`account`,`id`,`text`,`type`,`subject`,`status`,`tag`) VALUES ('.$db->quote($acc).','.$db->quote($id_next).','.$db->quote($_POST['text']).','.$db->quote($type).','.$db->quote($_POST['subject']).','.$db->quote($status).','.$db->quote($_POST['tags']).')');
                        header('Location: index.php?subtopic=bugtracker&id='.$id_next.'');
                    }
                        
                }
                echo '<br><form method="post" action=""><table><tr><td><i>Subject</i></td><td><input type=text name="subject"/></td></tr><tr><td><i>Description</i></td><td><textarea name="text" rows="15" cols="35"></textarea></td></tr><tr><td>TAG</td><td><select name="tags"><option value="">SELECT</option>';
                
                for($i = 1; $i <= count($tags); $i++)
                {
                    echo '<option value="' . $i . '">' . $tags[$i] . '</option>';
                }
                
                echo '</select></tr></tr></table><br><input type="submit" name="submit" value="Submit" class="input2"/></form>';
            }
        }
    }
    
    if(admin() and empty($_REQUEST['control']))
    {
        echo '<br><br><a href="index.php?subtopic=bugtracker&control=true">[ADMIN PANEL]</a>';
    }
}
else
{
    echo 'Please enter your account name and your password.<br/><a href="?subtopic=createaccount" >Create an account</a> if you do not have one yet.<br/><br/><form action="?subtopic=bugtracker" method="post" ><div class="TableContainer" >  <table class="Table1" cellpadding="0" cellspacing="0" >    <div class="CaptionContainer" >      <div class="CaptionInnerContainer" >        <span class="CaptionEdgeLeftTop" style="background-image:url('.$template_path.'/images/content/box-frame-edge.gif);" /></span>        <span class="CaptionEdgeRightTop" style="background-image:url('.$template_path.'/images/content/box-frame-edge.gif);" /></span>        <span class="CaptionBorderTop" style="background-image:url('.$template_path.'/images/content/table-headline-border.gif);" ></span>        <span class="CaptionVerticalLeft" style="background-image:url('.$template_path.'/images/content/box-frame-vertical.gif);" /></span>        <div class="Text" >Account Login</div>        <span class="CaptionVerticalRight" style="background-image:url('.$template_path.'/images/content/box-frame-vertical.gif);" /></span>        <span class="CaptionBorderBottom" style="background-image:url('.$template_path.'/images/content/table-headline-border.gif);" ></span>        <span class="CaptionEdgeLeftBottom" style="background-image:url('.$template_path.'/images/content/box-frame-edge.gif);" /></span>        <span class="CaptionEdgeRightBottom" style="background-image:url('.$template_path.'/images/content/box-frame-edge.gif);" /></span>      </div>    </div>    <tr>      <td>        <div class="InnerTableContainer" >          <table style="width:100%;" ><tr><td class="LabelV" ><span >Account Name:</span></td><td style="width:100%;" ><input type="password" name="account_login" SIZE="10" maxlength="10" ></td></tr><tr><td class="LabelV" ><span >Password:</span></td><td><input type="password" name="password_login" size="30" maxlength="29" ></td></tr>          </table>        </div>  </table></div></td></tr><br/><table width="100%" ><tr align="center" ><td><table border="0" cellspacing="0" cellpadding="0" ><tr><td style="border:0px;" ><div class="BigButton" style="background-image:url('.$template_path.'/images/buttons/sbutton.gif)" ><div onMouseOver="MouseOverBigButton(this);" onMouseOut="MouseOutBigButton(this);" ><div class="BigButtonOver" style="background-image:url('.$template_path.'/images/buttons/sbutton_over.gif);" ></div><input class="ButtonText" type="image" name="Submit" alt="Submit" src="'.$template_path.'/images/buttons/_sbutton_submit.gif" ></div></div></td><tr></form></table></td><td><table border="0" cellspacing="0" cellpadding="0" ><form action="?subtopic=lostaccount" method="post" ><tr><td style="border:0px;" ><div class="BigButton" style="background-image:url('.$template_path.'/images/buttons/sbutton.gif)" ><div onMouseOver="MouseOverBigButton(this);" onMouseOut="MouseOutBigButton(this);" ><div class="BigButtonOver" style="background-image:url('.$template_path.'/images/buttons/sbutton_over.gif);" ></div><input class="ButtonText" type="image" name="Account lost?" alt="Account lost?" src="'.$template_path.'/images/buttons/_sbutton_accountlost.gif" ></div></div></td></tr></form></table></td></tr></table>';
}
?> 
