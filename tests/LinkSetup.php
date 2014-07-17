<?php

/**
 * Setup the simple Links for TEsting
 * @since 1.17.13
 * @author Mat Lipe
 * @requires Selenium
 */
class LinkSetup extends PHPUnit_Extensions_SeleniumTestCase
{
    
    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = 'D:\htdocs/test/screenshots/simple-links';
    protected $screenshotUrl = 'http://test.loc/screenshots/simple-links';
    
    //Add the links to create
    public $links = array(
                  array(
                         'title'       => 'Facebook',
                         'Account'     => 'lipemat',
                         'My_Usage'    => 'Stop by once in a while to stir up the pot',
                         'web_address' => 'http://facebook.com',
                         'description' => 'A Social Networking Site',
                         'target' => 'blank',
                         'categories'  => 'Social Media', 
                  ),
                  array(
                          'title'       => 'Myspace',
                          'Account'     => 'Mat Lipe',
                          'My_Usage'    => 'Yeah right, noone goes here anymore',
                          'web_address' => 'http://myspace.com',
                          'description' => 'A Social Networking Site',
                          'target' => 'blank',
                          'categories'  => 'Social Media',
                   ),
                   array(
                           'title'       => 'Ebay',
                           'Account'     => 'starleigha',
                           'My_Usage'    => 'Not used to often now that Amazon took over',
                           'web_address' => 'http://ebay.com',
                           'description' => 'A site for online sales',
                           'target' => 'blank',
                           'categories'  => 'Ecom',
                   ),
                   array(
                           'title'       => 'Paypal',
                           'Account'     => 'lipeimagination',
                           'My_Usage'    => 'Used for receiving donations',
                           'web_address' => 'http://paypal.com',
                           'description' => 'An online money exchange - the safest way to pay',
                           'target' => 'blank',
                           'categories'  => 'Ecom',
                   ),
                   array(
                           'title'       => 'Wordpress',
                           'Account'     => 'Mat Lipe',
                           'My_Usage'    => 'Highly involved in plugin and theme development',
                           'web_address' => 'http://wordpress.org',
                           'description' => 'Open source CMS which this is built on',
                           'target' => 'blank',
                           'categories'  => array('Mat\'s Adventures','Social Media'),
                   ),
                   array(
                         'title'       => 'Selenium',
                         'Account'     => 'no Account yet',
                         'My_Usage'    => 'Primary test development',
                         'web_address' => 'http://seleniumhq.org/',
                         'description' => 'Automated Function and Unit Testing',
                         'target' => 'blank',
                         'categories'  => 'Mat\'s Adventures', 
                  ),
                   array(
                         'title'       => 'Amazon',
                         'Account'     => 'lipemat',
                         'My_Usage'    => 'My wife pretty much buys everything from here',
                         'web_address' => 'http://amazon.com',
                         'description' => 'The New online place to shop',
                         'target' => 'blank',
                         'categories'  => array('Ecom','Social Media'), 
                  )

            );
 
    
    
  protected function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://wordpress.loc/wp-admin");

  }
  

  /**
   * Creates all the needed links
   */
  public function testSetupAllLinks()
  {
      
    self::login();
    self::addAdditionalFields();
    self::addCategories();

    
    //Add all the links set in the class var
    foreach( $this->links as $link ){
        $this->click("link=Add Link");
        $this->waitForPageToLoad("30000");
        $this->type("id=title", $link['title']);
        $this->type("name=link_additional_value[Account]", $link['Account']);
        $this->type("css=input[name=\"link_additional_value[My Usage]\"]", $link['My_Usage']);
        $this->type("name=web_address", $link['web_address']);
        $this->type("name=description", $link['description']);
        $this->click("id=link_target_".$link['target']);
        
        if( is_array( $link['categories'] ) ){
            foreach( $link['categories'] as $cat ){
                $this->click("//label[contains(text(),\"".$cat."\")]");
            }
        } else {
            $this->click("//label[contains(text(),\"".$link['categories']."\")]");
        }
        $this->click("id=publish");
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
  
  
  /**
   * Adds the additional fields
   */
  protected function addAdditionalFields(){
      
      $this->click("xpath=(//li[@id='menu-posts-simple_link']/a/div[3])");
      $this->waitForPageToLoad("30000");
      
      if( !$this->isElementPresent("xpath=(//a[contains(@href, 'edit.php?post_type=simple_link&page=simple-link-settings')])") ){
            echo 'Is the test user an Admin???';
          die();   
          
      }
      $this->click("xpath=(//a[contains(@href, 'edit.php?post_type=simple_link&page=simple-link-settings')])");
      $this->waitForPageToLoad("30000");
      

      #-- Add additional Fields
      try {
          $this->getValue("name=link_additional_fields[]") == 'Account';
      } catch (Exception $e) {
          $this->type("css=input[name=\"link_additional_fields[] value=\"]", "Account");
          $this->click("id=simple-link-additional");
          $this->type("xpath=(//input[@name='link_additional_fields[] value='])[3]", "My Usage");
          $this->click("id=simple-link-additional");
          $this->click("id=submit");
          $this->waitForPageToLoad("30000");
      }

  }
  

      /**
       * Adds the Link Categories
       * @since 1.17.13
       */
      function addCategories(){
   
          if( $this->isElementPresent("xpath=(//a[contains(text(),'Link Categories')])[2]") ){
               $this->click("xpath=(//a[contains(text(),'Link Categories')])[2]");
          } else {
              $this->click("xpath=(//a[contains(text(),'Link Categories')])");
          }
          
          $this->waitForPageToLoad("30000");
          $this->click("css=#cb > input[type=\"checkbox\"]");
          $this->select("name=action", "label=Delete");
          $this->click("id=doaction");
          $this->waitForPageToLoad("30000");
          $this->type("id=tag-name", "Ecom");
          $this->type("id=tag-description", "Sites which feature online selling and buying");
          $this->click("id=submit");
          //$this->waitForPageToLoad("30000");
          $this->type("id=tag-name", "Social Media");
          $this->type("id=tag-description", "Networking online with friends or enemies");
          $this->click("id=submit");
          //$this->waitForPageToLoad("30000");
          $this->type("id=tag-name", "Mat's Adventures");
          $this->type("id=tag-description", "A category meant to have a tricky name to make sure things don't break on single quotes");
          $this->click("id=submit");
      }
  

}
?>