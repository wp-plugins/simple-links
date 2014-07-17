<?php
class Cleanup extends PHPUnit_Extensions_SeleniumTestCase
{
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://wordpress.loc/wp-admin");
  }

  /** 
   * Remove all data created by this test
   */
  public function testRemoveAllLinkData()
  {
      
    self::login();  
      
      
    $this->open("/wp-admin/edit.php?post_type=simple_link");
    $this->click("css=#cb > input[type=\"checkbox\"]");
    $this->select("name=action", "label=Move to Trash");
    $this->click("id=doaction");
    $this->waitForPageToLoad("30000");
    $this->click("css=#cb > input[type=\"checkbox\"]");
    $this->select("name=action", "label=Move to Trash");
    $this->click("id=doaction");
    $this->click("xpath=(//a[contains(@href, 'edit-tags.php?taxonomy=simple_link_category&post_type=simple_link')])");
    $this->waitForPageToLoad("30000");
    $this->click("css=#cb > input[type=\"checkbox\"]");
    $this->select("name=action", "label=Delete");
    $this->click("id=doaction");
    $this->waitForPageToLoad("30000");
    $this->click("link=Settings");
    $this->waitForPageToLoad("30000");
    try {
        $this->getValue("name=link_additional_fields[]") == 'Account';
    } catch (Exception $e) {
        $none = true;
    }
    if( !isset($none) ){
        $this->type("xpath=(//input[@value='My Usage'])", "");
        $this->type("xpath=(//input[@value='Account'])", "");
        $this->click("id=submit");
        $this->waitForPageToLoad("30000");
    }
    
    
  }
  
  
  

  /**
   * Logs into wordpress default user
   */
  protected function login(){
      $this->open("/wp-admin/" );
      $this->type("id=user_login" , "test" );
      $this->type("id=user_pass" , 'test' );
      $this->click("id=wp-submit" );
      $this->waitForPageToLoad("30000");
  
  }
  
}
?>