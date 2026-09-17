<?php

namespace App\Enums;

enum TeamPermission: string
{
    case UpdateTeam = 'team:update';
    case DeleteTeam = 'team:delete';

    case AddMember = 'member:add';
    case UpdateMember = 'member:update';
    case RemoveMember = 'member:remove';

    case CreateInvitation = 'invitation:create';
    case CancelInvitation = 'invitation:cancel';
    case ViewProjects = 'project:view';
    case CreateProject = 'project:create';
    case UpdateProject = 'project:update';
    case DeleteProject = 'project:delete';

    case ViewTestimonials = 'testimonial:view';
    case ApproveTestimonial = 'testimonial:approve';
    case DenyTestimonial = 'testimonial:deny';
    case DeleteTestimonial = 'testimonial:delete';

    case ChargeCustomer = 'customer:charge';
}
