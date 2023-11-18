import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import React from 'react';
import Edit from "./Edit.jsx";

registerBlockType('upcoming-events-lists/events', {
    apiVersion: 2,
    title: __('Upcoming Events List', 'upcoming-events-lists'),
    icon: 'calendar-alt',
    category: 'widgets',
    edit: Edit,

    save: () => null
});
