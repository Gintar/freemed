<?php
  # file: select_printers.php3
  # note: module to manage selectable printers 
  # code: max klohn <amk@span.ch>
  #       jeff b (jeff@univrel.pr.uconn.edu) -- template
  # lic : GPL
  # ver : 19991022
  # 
  # please note that you _can_ remove the comments down below,
  # but everything above here should remain untouched. please
  # do _not_ remove my name or address from this file, since I
  # have worked very hard on it. the license must also always
  # remain GPL.                                     -- jeff b

    // *** local variables section ***
    // complete these to reflect the data for this
    // module.

  $page_name="select_printers.php3";   // for help info, later
  $db_name  ="printer";                // get this from jeff
  $record_name="$Selectable_Printer"; // such as Room for Rooms module
                                       // or "CPT Modifiers" for cptmod
  $order_field="prnthost, prntname";   // what field the records are
                                       // sorted by... multiples can
                                       // be used with commas
                                       // ("value_a, value_b")
  $separate_add_section=true;          // if you need the addform action
                                       // keep this, if not, set to false

    // *** includes section ***

  include ("global.var.inc");         // load global variables
  include ("freemed-functions.inc");  // API functions

    // *** setting _ref cookie ***
    // if you are going to be "chaining" out from this
    // function and want users to be able to return to
    // it, uncomment this and it will set the cookie to
    // return people using the bar.
  //SetCookie("_ref", $page_name, time()+$_cookie_expire);

    // *** authorizing user ***

  freemed_open_db ($LoginCookie);  // authenticate user

    // *** initializing page ***

  freemed_display_html_top ();  // generate top of page
  freemed_display_banner ();    // display package banner

// *** main action loop ***
// (default action is "view")

if (($action=="addform") AND ($separate_add_section)) {

  freemed_display_box_top ("$Add $record_name", $page_name);

  echo "
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <TABLE WIDTH=100% BORDER=0 CELLPADDING=2>
    <TR><TD>    
    <$STDFONT_B>$Printer_Name :<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=\"prntname\" SIZE=25 MAXLENGTH=50 
     VALUE=\"$prntname\">
    </TD></TR>
    <TR><TD> 
    <$STDFONT_B>$Printer_Host :<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=\"prnthost\" SIZE=25 MAXLENGTH=50
     VALUE=\"$prnthost\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Access_Level (0-9) :<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=\"prntaclvl\" SIZE=2 MAXLENGTH=2
     VALUE=\"$prntaclvl\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Description :<$STDFONT_E>
    </TD><TD>
    <TEXTAREA NAME=\"prntdesc\" ROWS=4 COLS=25
     WRAP=VIRTUAL>$prntdesc</TEXTAREA>
    </TD></TR>
    </TABLE>
    <BR>


    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Add \"  >
    <INPUT TYPE=RESET  VALUE=\" $Clear \">
    </CENTER></FORM>
    <P>
    ";


  // do some tricks if /etc/printcap is found
     $printcap   = "/etc/printcap"           ;
     $found_file = file_exists ( $printcap ) ;
      //  echo "<P> found_file = $found_file <P> ";
      if ( $found_file == 1 ) {

      $_alternate = freemed_bar_alternate_color ($_alternate);

        $fq = fopen ( $printcap , "r") ;
             if (!($fq)) {
                echo "file $printcap could not be opened" ; 
                exit ;
                         }
     $printer_list = fread ( $fq , filesize( $printcap ) ) ; 
       // error handler here

     $fileclose = fclose ( $fq )                      ;
       // error-handling code here
     
      $printer_list = nl2br ( $printer_list )         ;

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%  >
      <TR><TD BGCOLOR=$_alternate>
      <CENTER>$printcap $Current_Contents ($For_Reference) :</CENTER>
      </TD></TR>
         ";

      $_alternate = freemed_bar_alternate_color ($_alternate);

    echo " 
      <TR><TD BGCOLOR=$_alternate>
      <TT>$printer_list</TT>
      </TD><TR>
      </TABLE>
         ";
                              }  // end printcap stuff



  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Addition</A>
    </CENTER>
  ";

} elseif ($action=="add") {

  freemed_display_box_top("$Adding $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Adding . . . 
  ";

    // prepare data: remove the 's, etc from the blob

  $prntdesc = addslashes ($prntdesc) ;


    // build the query to database backend (usually MySQL):
    // the last value has to be NULL so that it auto
    // increments record numbers.
  $query = "INSERT INTO $database.$db_name VALUES ( ".
    "'$prntname', '$prnthost', '$prntaclvl', '$prntdesc', NULL ) ";

    // query the db with new values
  $result = fdb_query($query);

  if ($debug==1) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;      
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  }

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  "; // readability fix 19990714

  freemed_display_box_bottom (); // display the bottom of the box
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="modform") {

  freemed_display_box_top ("$Modify $record_name", $page_name);

  # here, we have the difference between adding and
  # modifying...

  if (strlen($id)<1) {
    echo "

     <B><CENTER>$Please_use_MODIFY
       $record_name !</B>
     </CENTER>

     <P>
    ";

    if ($debug==1) {
      echo "
        ID = [<B>$id</B>]
        <P>
      ";
    }

    freemed_display_box_bottom (); // display the bottom of the box
    echo "
      <CENTER>
      <A HREF=\"main.php3?$_auth\"
       >$Return_to_the_Main_Menu</A>
      </CENTER>
    ";
    DIE("");
  }

  // if there _IS_ an ID tag presented, we must extract the record
  // from the database, and proverbially "fill in the blanks"

    // grab record number "id"
  $result = fdb_query("SELECT * FROM $database.$db_name ".
    "WHERE ( id = '$id' )");

    // display for debugging purposes
  if ($debug==1) {
    echo " <B>$RESULT</B> = [$result]<BR><BR> ";
  }

  $r = fdb_fetch_array($result); // dump into array r[]

    // this dumps the result of the query (the record to
    // be modified) into the variables with those names,
    // for easy use by us.
  $prntname    = $r["prntname"  ] ;
  $prnthost    = $r["prnthost"  ] ;
  $prntaclvl   = $r["prntaclvl" ] ;
  $prntdesc    = htmlentities ( $r["prntdesc"  ] ) ;

  //
  $prntdesc    = stripslashes ($prntdesc) ;


  echo "
    <FORM ACTION=\"$page_name\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"$id\"  >

    <TABLE WIDTH=100% BORDER=0 CELLPADDING=2>
    <TR><TD>
    <$STDFONT_B>$Printer Name :<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=\"prntname\" SIZE=25 MAXLENGTH=50
     VALUE=\"$prntname\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Printer_Host :<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=\"prnthost\" SIZE=25 MAXLENGTH=50
     VALUE=\"$prnthost\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Access_Level (0-9) :<$STDFONT_E>
    </TD><TD>
    <INPUT TYPE=TEXT NAME=\"prntaclvl\" SIZE=2 MAXLENGTH=2
     VALUE=\"$prntaclvl\">
    </TD></TR>
    <TR><TD>
    <$STDFONT_B>$Description :<$STDFONT_E>
    </TD><TD>
    <TEXTAREA NAME=\"prntdesc\" ROWS=4 COLS=25
     WRAP=VIRTUAL>$prntdesc</TEXTAREA>
    </TD></TR>
    </TABLE>
    <BR>
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" $Update \">
    <INPUT TYPE=RESET  VALUE=\"$Remove_Changes\">
    </CENTER></FORM>
    <P>
  ";


  // do some tricks if /etc/printcap is found
     $printcap   = "/etc/printcap"           ;
     $found_file = file_exists ( $printcap ) ;
      //  echo "<P> found_file = $found_file <P> ";
      if ( $found_file == 1 ) {

      $_alternate = freemed_bar_alternate_color ($_alternate);

        $fq = fopen ( $printcap , "r") ;
             if (!($fq)) {
                echo "file $printcap could not be opened" ; 
                exit ;
                         }
     $printer_list = fread ( $fq , filesize( $printcap ) ) ; 
       // error handler here

     $fileclose = fclose ( $fq )                      ;
       // error-handling code here
     
      $printer_list = nl2br ( $printer_list )         ;

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%  >
      <TR><TD BGCOLOR=$_alternate>
      <CENTER>$printcap $Current_Contents ($For_Reference) :</CENTER>
      </TD></TR>
         ";

      $_alternate = freemed_bar_alternate_color ($_alternate);

    echo " 
      <TR><TD BGCOLOR=$_alternate>
      <TT>$printer_list</TT>
      </TD><TR>
      </TABLE>
         ";
                              }  // end printcap stuff



  freemed_display_box_bottom (); // show the bottom of the box

  echo "
    <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Abandon_Modification</A>
    </CENTER>
  ";

} elseif ($action=="mod") {

   #      M O D I F Y - R O U T I N E

  freemed_display_box_top ("$Modifying $record_name", $page_name);

  echo "
    <P>
    <$STDFONT_B>$Modifying . . . 
  ";

  // prepare data

  $prntdesc = addslashes ($prntdesc) ;

    // build update query:
    // only set the values that need to be
    // changed... for example, don't set the
    // creation date in a modify. also,
    // remember the commas...
  $query = "UPDATE $database.$db_name SET ".
    "prntname='$prntname', ".
    "prnthost='$prnthost', ". 
    "prntaclvl='$prntaclvl', ".
    "prntdesc='$prntdesc' ".
    "WHERE id='$id' ";

  $result = fdb_query($query); // execute query

  if ($debug==1) {
    echo "\n<BR><BR><B>QUERY RESULT:</B><BR>\n";
    echo $result;
    echo "\n<BR><BR><B>QUERY STRING:</B><BR>\n";
    echo "$query";
    echo "\n<BR><BR><B>ACTUAL RETURNED RESULT:</B><BR>\n";
    echo "($result)";
  }

  if ($result) {
    echo "
      <B>$Done.</B><$STDFONT_E>
    ";
  } else {
    echo ("<B>$ERROR ($result)</B>\n"); 
  } // end of error reporting clause

  echo "
    <P>
    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>
    <P>
  "; // usability patch 19990714

  freemed_display_box_bottom (); // display box bottom 
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="del") {

  freemed_display_box_top ("$Deleting $record_name", $page_name);

    // select only "id" record, and delete
  $result = fdb_query("DELETE FROM $database.$db_name
    WHERE (id = \"$id\")");

  echo "
    <BR><BR>
    <I>$record_name <B>$id</B> $Deleted<I>.
    <BR>
  ";
  if ($debug==1) {
    echo "
      <BR><B>$RESULT :</B><BR>
      $result<BR><BR>
    ";
  }
  echo "
    <BR><CENTER>
    <A HREF=\"$page_name?$_auth&action=view\"
     >$Update_Delete_Another</A></CENTER>
  ";
  freemed_display_box_bottom ();
  freemed_display_bottom_links ($record_name, $page_name, $_ref);

} elseif ($action=="export") {

  freemed_display_box_top ("$Export $record_name", $_ref, $page_name);

  echo "
    <P>
    <$STDFONT_B>$Exporting_data ...
  ";

  $result = freemed_export_stock_data ($db_name);

  if ($result) echo "$Done.";
   else echo "$ERROR";

  echo "
    <P>

    <CENTER><A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>$Return_to $record_name $Menu<$STDFONT_E></A>
    </CENTER>

    <P>
  ";

  freemed_display_box_bottom ();

} else {

  // with no anythings, ?action=search returns everything
  // in the database for modification... useful to note in
  // future...


  $query = "SELECT * FROM $database.$db_name ".
   "ORDER BY $order_field";

  $result = fdb_query($query);
  if ($result) {
    freemed_display_box_top ($record_name, $_ref, $page_name);

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    // if you would rather have the add form built onto the view
    // menu, uncomment the next few lines. 

    //echo "
    //  <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
    //   CELLSPACING=0 CELLPADDING=3>
    //  <TR BGCOLOR=#000000>
    //  <TD ALIGN=LEFT>&nbsp;</TD>
    //  <TD WIDTH=30%>&nbsp;</TD>
    //  <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
    //   ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
    //   SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
    //  </TR></TABLE>
    //";

    // and comment this line:
    freemed_display_actionbar($page_name, $_ref);

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%>
      <TR>
       <TD><B>$Printer</B></TD>
       <TD><B>$Host</B></TD>
       <TD><B>$Action</B></TD>
      </TR>
    "; // header of box

    $_alternate = freemed_bar_alternate_color ();

    while ($r = fdb_fetch_array($result)) {

      $prntname  = $r["prntname"  ] ;
      $prnthost  = $r["prnthost"  ] ;
      $id        = $r["id"        ] ;

        // alternate the bar color
      $_alternate = freemed_bar_alternate_color ($_alternate);

      if ($debug==1) {
        $id_mod = " [$id]"; // if debug, insert ID #
      } else {
        $id_mod = ""; // else, let's avoid it...
      } // end debug clause (like sanity clause)

      echo "
        <TR BGCOLOR=$_alternate>
        <TD>$prntname</TD>
        <TD><I>$prnthost</I>&nbsp;</TD>
        <TD><A HREF=
         \"$page_name?$_auth&id=$id&action=modform\"
         ><FONT SIZE=-1>$MOD$id_mod</FONT></A>
      ";
      if (freemed_get_userlevel($LoginCookie)>$delete_level)
        echo "
          &nbsp;
          <A HREF=\"$page_name?$_auth&id=$id&action=del\"
          ><FONT SIZE=-1>$DEL$id_mod</FONT></A>
        "; // show delete
      echo "
        </TD></TR>
      ";

    } // while there are no more

      // now, we put the add table part...
      // uncomment if needed.

    //$_alternate = freemed_bar_alternate_color ($_alternate);
    //echo "
    //  <TR BGCOLOR=$_alternate VALIGN=CENTER>
    //  <TD VALIGN=CENTER><FORM ACTION=\"$page_name\"
    //   ><INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
    //   <INPUT TYPE=TEXT NAME=\"value_a\" SIZE=3
    //    MAXLENGTH=2></TD>
    //  <TD VALIGN=CENTER>
    //   <INPUT TYPE=TEXT NAME=\"value_b\" SIZE=20
    //    MAXLENGTH=30></TD>
    //  <TD VALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=\"ADD\"></FORM></TD>
    //  </TR>
    //";

    echo "
      </TABLE>
    "; // end table (fixed 19990617)

    if (strlen($_ref)<5) {
      $_ref="main.php3";
    } // if no ref, then return to home page...

    //  if you would rather have the add form built onto the view
    //  menu, just uncomment the next few lines for a bar without
    //  the add function...

    //echo "
    //  <TABLE BGCOLOR=#000000 WIDTH=100% BORDER=0
    //   CELLSPACING=0 CELLPADDING=3>
    //  <TR BGCOLOR=#000000>
    //  <TD ALIGN=LEFT>&nbsp;</TD>
    //  <TD WIDTH=30%>&nbsp;</TD>
    //  <TD ALIGN=RIGHT><A HREF=\"$_ref?$_auth\"
    //   ><FONT COLOR=#ffffff FACE=\"Arial, Helvetica, Verdana\"
    //   SIZE=-1><B>RETURN TO MENU</B></FONT></A></TD>
    //  </TR></TABLE>
    //";

    // then comment this:
    freemed_display_actionbar ($page_name, $_ref);

    if (freemed_get_userlevel ($LoginCookie) > $export_level)
      echo "
        <BR>
        <A HREF=\"$page_name?$_auth&action=export\"
         ><$STDFONT_B>$Export_Data<$STDFONT_E></A>
        <BR>
      ";

  // help 


   echo "
   <P>
   <A HREF=\"help.php3?$_auth&page_name=$page_name\"
   TARGET=\"__HELP__\">$Help_for</A>
  <P>
        ";

  // do some tricks if /etc/printcap is found
     $printcap   = "/etc/printcap"           ;
     $found_file = file_exists ( $printcap ) ;
      //  echo "<P> found_file = $found_file <P> ";
      if ( $found_file == 1 ) {

      $_alternate = freemed_bar_alternate_color ($_alternate);

        $fq = fopen ( $printcap , "r") ;
             if (!($fq)) {
                echo "file $printcap could not be opened" ; 
                exit ;
                         }
     $printer_list = fread ( $fq , filesize( $printcap ) ) ; 
       // error handler here

     $fileclose = fclose ( $fq )                      ;
       // error-handling code here
     
      $printer_list = nl2br ( $printer_list )         ;

    echo "
      <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=100%  >
      <TR><TD BGCOLOR=$_alternate>
      <CENTER>$printcap $Current_Contents ($For_Reference) :</CENTER>
      </TD></TR>
         ";

      $_alternate = freemed_bar_alternate_color ($_alternate);

    echo " 
      <TR><TD BGCOLOR=$_alternate>
      <TT>$printer_list</TT>
      </TD><TR>
      </TABLE>
         ";
                              }  // end printcap stuff

    freemed_display_box_bottom (); // display bottom of the box

  } else {
    echo "\n<B>$No_Record_Found</B>\n";
  }

} 

freemed_close_db(); // always close the database when done!
freemed_display_html_bottom (); // starting here, combined php3 code areas

?>
