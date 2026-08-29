<?php

namespace App\Mail;

/**
 * Backward compatibility alias for queued jobs that were dispatched
 * when TouristWelcomeMail was previously named WelcomeUserMail.
 */
class WelcomeUserMail extends TouristWelcomeMail
{
}
