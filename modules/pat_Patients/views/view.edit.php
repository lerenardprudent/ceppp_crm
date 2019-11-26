<?php
/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2018 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 */
require_once('include/MVC/View/views/view.edit.php');
require_once('include/SugarTinyMCE.php');

class pat_PatientsViewEdit extends ViewEdit
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @deprecated deprecated since version 7.6, PHP4 Style Constructors are deprecated and will be remove in 7.8, please update your code, use __construct instead
     */
    public function pat_PatientsViewEdit()
    {
        $deprecatedMessage = 'PHP4 Style Constructors are deprecated and will be remove in 7.8, please update your code';
        if (isset($GLOBALS['log'])) {
            $GLOBALS['log']->deprecated($deprecatedMessage);
        } else {
            trigger_error($deprecatedMessage, E_USER_DEPRECATED);
        }
        self::__construct();
    }

    public function preDisplay()
    {
      parent::preDisplay();
      if ( !isset($this->bean->id) ) {
        $this->bean->code_ident = $this->generate_patient_id();
        $foo = 1;
      }
    }
    
    function generate_patient_id() {
      $datestamp = (new DateTime("now", new DateTimeZone('America/Montreal')))->format('Ymd');
      $rand = $this->genRandDigSeq(4);
      $codeIdent = $datestamp . "_" . $rand;
      while ( $this->patientAlreadyExists("code_ident='$codeIdent'") ) { // Check if user with same code already exists
        $rand = $this->genRandDigSeq(4);
        $codeIdent = $datestamp . "_" . $rand;
      }
      return $codeIdent;
    }
  
    function genRandDigSeq($nDigits) {
      while ( $nDigits-- > 0 ) {
        $seq .= rand(0,9);
      }
      return $seq;
    }
  
    function patientAlreadyExists($where) {
      $query = "SELECT 1 from pat_patients where $where AND deleted=0";
      $db = DBManagerFactory::getInstance();
      $result = $db->query($query, true, "");
      return $result->num_rows != 0;
    }
    
    public function display()
    {
        parent::display();

        $newScript = '';

        if (empty($this->bean->id)) {
            $newScript = "
                    console.log('HOOLY!');";
            $script .= "tinyMCE.execCommand('mceAddControl', false, document.getElementById('description'));";

            echo '<script>$(document).ready(function(){' . $script . '})</script>';
        }
        
        /* dmarg 20190827 */
        global $current_user;
        include_once('modules/ACLRoles/ACLRole.php');
        $roles = ACLRole::getUserRoleNames($current_user->id);
        
        $hidePanelScript = "";
        $roleNames = array_map('strtolower', $roles);
        foreach ( $roleNames as $roleName ) {
          $hidePanelScript .= "$('body').attr('data-role-$roleName', 1);";
        }
        $patPerspPanelChildDivId = "detailpanel_10";
        if (in_array("recruteur", $roleNames) ) {
          /*$hidePanelScript .= "$('#$patPerspPanelChildDivId').closest('.panel').hide();"; NOT HIDING ANYMORE */ 
        }
        echo '<script>$(document).ready(function(){' . $hidePanelScript . '})</script>';
        
        $customJsFilePath = 'modules/pat_Patients/custom.js';
        echo "<script type='text/javascript' src='$customJsFilePath'>";
    }
}
