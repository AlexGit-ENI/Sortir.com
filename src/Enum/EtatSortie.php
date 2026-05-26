<?php

namespace App\Enum;

enum EtatSortie: string
{
    case CREATED =  'Créée';
    case OPEN = 'Ouverte';
    case CLOSED = 'Clôturée';
    case CURRENT= 'En cours';
    case PAST = 'Passée';
    case CANCELLED = 'Annulé';
    case ARCHIVED = 'Archivé';
}
