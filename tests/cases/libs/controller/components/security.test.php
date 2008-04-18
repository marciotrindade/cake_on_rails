<?php
/* SVN FILE: $Id$ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package			cake.tests
 * @subpackage		cake.tests.cases.libs.controller.components
 * @since			CakePHP(tm) v 1.2.0.5435
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
uses('controller' . DS . 'components' . DS .'security');

/**
* Short description for class.
*
* @package		cake.tests
* @subpackage	cake.tests.cases.libs.controller.components
*/
class SecurityTestController extends Controller {
	var $name = 'SecurityTest';
	var $components = array('Security');

	function redirect($option, $code, $exit) {
		return $code;
	}
}

/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs.controller.components
 */
class SecurityComponentTest extends CakeTestCase {

	function setUp() {
		$this->Controller =& new SecurityTestController();
		restore_error_handler();
		@$this->Controller->_initComponents();
		set_error_handler('simpleTestErrorHandler');
	}

	function testStartup() {
		$this->Controller->Security->startup($this->Controller);
		$result = $this->Controller->params['_Token']['key'];
		$this->assertNotNull($result);
		$this->assertTrue($this->Controller->Session->check('_Token'));
	}
	
	function testRequirePost()
	{
		$this->Controller->action = 'posted';
		$this->Controller->Security->startup($this->Controller);
		$this->Controller->Security->requirePost('posted');
		$this->assertNull($this->Controller->Security->__postRequired($this->Controller));
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->assertTrue($this->Controller->Security->__postRequired($this->Controller));
	}
	
	function testRequireGet()
	{
		$this->Controller->action = 'getted';
		$this->Controller->Security->startup($this->Controller);
		$this->Controller->Security->requireGet('getted');
		$this->assertNull($this->Controller->Security->__getRequired($this->Controller));
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$this->assertTrue($this->Controller->Security->__getRequired($this->Controller));
	}
	
	function testRequirePut()
	{
		$this->Controller->action = 'putted';
		$this->Controller->Security->startup($this->Controller);
		$this->Controller->Security->requirePut('putted');
		$this->assertNull($this->Controller->Security->__putRequired($this->Controller));
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		$this->assertTrue($this->Controller->Security->__putRequired($this->Controller));
	}
	
	function testRequireDelete()
	{
		$this->Controller->action = 'deleted';
		$this->Controller->Security->startup($this->Controller);
		$this->Controller->Security->requireDelete('deleted');
		$this->assertNull($this->Controller->Security->__deleteRequired($this->Controller));
		$_SERVER['REQUEST_METHOD'] = 'DELETE';
		$this->assertTrue($this->Controller->Security->__deleteRequired($this->Controller));
	}
	
	function testValidatePostNoModel() {
		$this->Controller->Security->startup($this->Controller);
		$key = $this->Controller->params['_Token']['key'];

		$data['anything'] = 'some_data';
		$data['__Token']['key'] = $key;

		$fields = array('anything',
						'__Token' => array('key' => $key));
		
		$fields = $this->__sortFields($fields);

		$fields = urlencode(Security::hash(serialize($fields) . Configure::read('Security.salt')));
		$data['__Token']['fields'] = $fields;
		$this->Controller->data = $data;
		$result = $this->Controller->Security->__validatePost($this->Controller);
		$this->assertTrue($result);
		$this->assertTrue($this->Controller->data == $data);
	}

	function testValidatePostSimple() {
		$this->Controller->Security->startup($this->Controller);
		$key = $this->Controller->params['_Token']['key'];

		$data['Model']['username'] = '';
		$data['Model']['password'] = '';
		$data['__Token']['key'] = $key;

		$fields = array('Model' => array('username','password'),
						'__Token' => array('key' => $key));

		$fields = $this->__sortFields($fields);

		$fields = urlencode(Security::hash(serialize($fields) . Configure::read('Security.salt')));
		$data['__Token']['fields'] = $fields;
		$this->Controller->data = $data;
		$result = $this->Controller->Security->__validatePost($this->Controller);
		$this->assertTrue($result);
		$this->assertTrue($this->Controller->data == $data);
	}

	function testValidatePostCheckbox() {

		$this->Controller->Security->startup($this->Controller);
		$key = $this->Controller->params['_Token']['key'];

		$data['Model']['username'] = '';
		$data['Model']['password'] = '';
		$data['_Model']['valid'] = '0';
		$data['__Token']['key'] = $key;

		$fields = array('Model' => array('username', 'password', 'valid'),
						'_Model' => array('valid' => '0'),
						'__Token' => array('key' => $key));

		$fields = $this->__sortFields($fields);

		$fields = urlencode(Security::hash(serialize($fields) . Configure::read('Security.salt')));
		$data['__Token']['fields'] = $fields;

		$this->Controller->data = $data;
		$result = $this->Controller->Security->__validatePost($this->Controller);
		$this->assertTrue($result);

		unset($data['_Model']);
		$data['Model']['valid'] = '0';
		$this->assertTrue($this->Controller->data == $data);
	}

	function testValidatePostHidden() {
		$this->Controller->Security->startup($this->Controller);
		$key = $this->Controller->params['_Token']['key'];

		$data['Model']['username'] = '';
		$data['Model']['password'] = '';
		$data['_Model']['hidden'] = '0';
		$data['__Token']['key'] = $key;

		$fields = array('Model' => array('username', 'password', 'hidden'),
						'_Model' => array('hidden' => '0'),
						'__Token' => array('key' => $key));

		$fields = $this->__sortFields($fields);

		$fields = urlencode(Security::hash(serialize($fields) . Configure::read('Security.salt')));
		$data['__Token']['fields'] = $fields;

		$this->Controller->data = $data;
		$result = $this->Controller->Security->__validatePost($this->Controller);
		$this->assertTrue($result);

		unset($data['_Model']);
		$data['Model']['hidden'] = '0';
		$this->assertTrue($this->Controller->data == $data);
	}

	function testValidateHiddenMultipleModel() {
		$this->Controller->Security->startup($this->Controller);
		$key = $this->Controller->params['_Token']['key'];

		$data['Model']['username'] = '';
		$data['Model']['password'] = '';
		$data['_Model']['valid'] = '0';
		$data['_Model2']['valid'] = '0';
		$data['_Model3']['valid'] = '0';
		$data['__Token']['key'] = $key;

		$fields = array('Model' => array('username', 'password', 'valid'),
						'Model2'=> array('valid'),
						'Model3'=> array('valid'),
						'_Model2'=> array('valid' => '0'),
						'_Model3'=> array('valid' => '0'),
						'_Model' => array('valid' => '0'),
						'__Token' => array('key' => $key));

		$fields = $this->__sortFields($fields);

		$fields = urlencode(Security::hash(serialize($fields) . Configure::read('Security.salt')));
		$data['__Token']['fields'] = $fields;

		$this->Controller->data = $data;
		$result = $this->Controller->Security->__validatePost($this->Controller);
		$this->assertTrue($result);

		unset($data['_Model'], $data['_Model2'], $data['_Model3']);
		$data['Model']['valid'] = '0';
		$data['Model2']['valid'] = '0';
		$data['Model3']['valid'] = '0';
		$this->assertTrue($this->Controller->data == $data);
	}

	function testValidateHasManyModel() {
		$this->Controller->Security->startup($this->Controller);
		$key = $this->Controller->params['_Token']['key'];

		$data['Model'][0]['username'] = 'username';
		$data['Model'][0]['password'] = 'password';
		$data['Model'][1]['username'] = 'username';
		$data['Model'][1]['password'] = 'password';
		$data['_Model'][0]['hidden'] = 'value';
		$data['_Model'][1]['hidden'] = 'value';
		$data['_Model'][0]['valid'] = '0';
		$data['_Model'][1]['valid'] = '0';
		$data['__Token']['key'] = $key;

		$fields = array(
			'Model' => array(
				0 => array('username', 'password', 'valid'),
				1 => array('username', 'password', 'valid')),
			'_Model' => array(
				0 => array('hidden' => 'value', 'valid' => '0'),
				1 => array('hidden' => 'value', 'valid' => '0')),
			'__Token' => array('key' => $key));

		$fields = $this->__sortFields($fields);

		$fields = urlencode(Security::hash(serialize($fields) . Configure::read('Security.salt')));
		$data['__Token']['fields'] = $fields;

		$this->Controller->data = $data;
		$result = $this->Controller->Security->__validatePost($this->Controller);
		$this->assertTrue($result);

		unset($data['_Model']);
		$data['Model'][0]['hidden'] = 'value';
		$data['Model'][1]['hidden'] = 'value';
		$data['Model'][0]['valid'] = '0';
		$data['Model'][1]['valid'] = '0';

		$this->assertTrue($this->Controller->data == $data);
  }

	function __sortFields($fields) {
		foreach ($fields as $key => $value) {
			if(strpos($key, '_') !== 0 && is_array($fields[$key])) {
				sort($fields[$key]);
			}
		}
		ksort($fields, SORT_STRING);
		return $fields;
	}
}
?>
