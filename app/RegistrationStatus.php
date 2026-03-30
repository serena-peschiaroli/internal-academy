<?php

namespace App;

enum RegistrationStatus: string
{
    case CONFIRMED = 'confirmed';
    case WAITLISTED = 'waitlisted';
}
