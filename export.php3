<?php
 # file: export.php3
 # desc: administrative export module
 # code: jeff b (jeff@univrel.pr.uconn.edu)
 # lic : GPL, v2

 $page_name = "export.php3";
 include ("global.var.inc");
 include ("freemed-functions.inc");

 freemed_open_db ($LoginCookie);
 $this_user = new User ($LoginCookie);

freemed_display_html_top ();
freemed_display_banner ();

if ($this_user->getLevel()<$admin_level)
 DIE("$page_name :: You do not have access to this module");

switch ($action) {
 case "export":
  freemed_display_box_top ("Export Database");
  echo "
   <P>
   <$STDFONT_B>Exporting Database \"$db\" ... 
  ";
  if (freemed_export_stock_data ($db)) { echo "$Done."; }
   else                                { echo "$ERROR"; }
  echo "
   <$STDFONT_E>
   <P>
    <CENTER>
    <A HREF=\"$page_name?$_auth\"
     ><$STDFONT_B>Export Another Database<$STDFONT_E></A> <B>|</B>
    <A HREF=\"admin.php3?$_auth\"
     ><$STDFONT_B>Return to Administration Menu<$STDFONT_E></A>
    </CENTER>
   <P>
  ";
  freemed_display_box_bottom ();
  break;
 default:
  freemed_display_box_top ("Export Database");
  echo "
   <FORM ACTION=\"$page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"export\">
    <P>
    <$STDFONT_B>Select Database to Export : <$STDFONT_E>
    <SELECT NAME=\"db\">
     <OPTION VALUE=\"room\"        >Booking Locations (room)
     <OPTION VALUE=\"roomequip\"   >Booking Locations Equipment (roomequip)
     <OPTION VALUE=\"callin\"      >Call-In Patients (callin)
     <OPTION VALUE=\"cpt\"         >CPT/Procedural Codes (cpt)
     <OPTION VALUE=\"cptmod\"      >CPT Modifiers (cptmod)
     <OPTION VALUE=\"patrecdata\"  >Custom Records (patrecdata)
     <OPTION VALUE=\"patrectemplate\"
                                   >Custom Record Templates (patrectemplate)
     <OPTION VALUE=\"degrees\"     >Degrees (degrees)
     <OPTION VALUE=\"diagfamily\"  >Diagnosis Families (diagfamily)
     <OPTION VALUE=\"eoc\"         >Episodes of Care (eoc)
     <OPTION VALUE=\"infaxlut\"    >Fax Sender Lookup Table (infaxlut)
     <OPTION VALUE=\"fixedform\"   >Fixed-Length Forms (fixedform)
     <OPTION VALUE=\"frmlry\"      >Formulary/Drugs (frmlry)
     <OPTION VALUE=\"icd9\"        >ICD/Diagnosis Codes (icd9)
     <OPTION VALUE=\"infaxes\"     >Incoming Faxes (infaxes)
     <OPTION VALUE=\"insco\"       >Insurance Companies (insco)
     <OPTION VALUE=\"inscogroyp\"  >Insurance Company Groups (inscogroup)
     <OPTION VALUE=\"intservtype\" >Internal Service Types (inservtype)
     <OPTION VALUE=\"log\"         >Log File (log)
     <OPTION VALUE=\"oldreports\"  >Old Reports (oldreports)
     <OPTION VALUE=\"patimg\"      >Patient Images (patimg)
     <OPTION VALUE=\"patient\"     >Patient Record (patient)
     <OPTION VALUE=\"payrec\"      >Payment/Ledget Records (payrec)
     <OPTION VALUE=\"phyavailmap\" >Physician Availability Map (phyavailmap)
     <OPTION VALUE=\"physician\"   >Physicians/Providers (physician)
     <OPTION VALUE=\"phygroup\"    >Physician/Provider Group (phygroup)
     <OPTION VALUE=\"phystatus\"   >Physician/Provider Status (phystatus)
     <OPTION VALUE=\"facility\"    >Place of Service (facility) 
     <OPTION VALUE=\"rx\"          >Prescriptions (rx)
     <OPTION VALUE=\"printer\"     >Printers (printer)
     <OPTION VALUE=\"procedure\"   >Procedures (procedure)
     <OPTION VALUE=\"pnotes\"      >Progress Notes (pnotes)
     <OPTION VALUE=\"scheduler\"   >Scheduler (scheduler)
     <OPTION VALUE=\"simplereport\">Simple Reports (simplereport)
     <OPTION VALUE=\"specialties\" >Specialties (specialties)
     <OPTION VALUE=\"tos\"         >Type of Service (tos)    
    </SELECT>
    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\"Export\">
    </CENTER>
    <P>
    <CENTER>
     <A HREF=\"admin.php3?$_auth\"
     ><$STDFONT_B>Return to Administration Menu<$STDFONT_E></A>
    </CENTER>
    <P>
   </FORM>
  ";
  freemed_display_box_bottom ();
  break;
} // end action switch

freemed_close_db ();
freemed_display_html_bottom ();

?>
