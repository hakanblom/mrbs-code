<?php
namespace MRBS\Session;

use MRBS\User;
use \phpCAS;
use MRBS\Form\Form;
use function MRBS\auth;


class SessionCas extends SessionWithLogin
{

  public function __construct()
  {
    $this->checkTypeMatchesSession();
    auth()->init();  // Initialise CAS
    parent::__construct();
  }


  public function authGet(?string $target_url=null, ?string $returl=null, ?string $error=null, bool $raw=false) : void
  {
    // Useless Method - CAS does it all
  }


  public function getCurrentUser() : ?User
  {
    return (phpCAS::isAuthenticated()) ? auth()->getUser(phpCAS::getUser()) : null;
  }


  public function getLogonFormParams() : ?array
  {
    $target_url = \MRBS\this_page(true);

    return array(
        'action' => $target_url,
        'method' => 'post',
        'hidden_inputs' =>  array('target_url' => $target_url,
                                  'action'     => 'QueryName')
      );
  }


  public function processForm() : void
  {
    if (isset($this->form['action']))
    {
      // Target of the form with sets the URL argument "action=QueryName".
      if ($this->form['action'] == 'QueryName')
      {
        phpCAS::forceAuthentication();
      }

      // Target of the form with sets the URL argument "action=SetName".
      // Will eventually return to URL argument "target_url=whatever".
      if ($this->form['action'] == 'SetName')
      {
        // If we're going to do something then check the CSRF token first
        Form::checkToken();

        // You should only get here using CAS authentication after clicking the logoff
        // link, no matter what the value of the form parameters.
        $this->logoffUser();

        \MRBS\location_header($this->form['target_url']); // Redirect browser to initial page
      }
    }
  }


  public function logoffUser() : void
  {
    phpCAS::logout();
  }

}
