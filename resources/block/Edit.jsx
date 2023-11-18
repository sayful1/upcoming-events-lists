import {InspectorControls, useBlockProps} from "@wordpress/block-editor";
import {PanelBody, SelectControl, ToggleControl} from "@wordpress/components";
import ServerSideRender from '@wordpress/server-side-render';
import {__} from "@wordpress/i18n";
import React from "react";

const columnsOptions = [
    {label: __('Default (as Global Settings)', 'upcoming-events-lists'), value: '0'},
    {label: __('1 Column', 'upcoming-events-lists'), value: '1'},
    {label: __('2 Columns', 'upcoming-events-lists'), value: '2'},
    {label: __('3 Columns', 'upcoming-events-lists'), value: '3'},
    {label: __('4 Columns', 'upcoming-events-lists'), value: '4'},
    {label: __('6 Columns', 'upcoming-events-lists'), value: '6'},
];

export default function Edit({attributes, setAttributes}) {
    const {
        view_type,
        show_all_event_link,
        columns_on_phone,
        columns_on_tablet,
        columns_on_desktop,
        columns_on_widescreen
    } = attributes
    const InspectorControlsEl = (
        <InspectorControls key="setting">
            <PanelBody
                title={__('General Options', 'upcoming-events-lists')}
                initialOpen={true}
            >
                <div className="upcoming-events-lists-select-control">
                    <SelectControl
                        label={__('Display Type', 'upcoming-events-lists')}
                        value={view_type}
                        options={[
                            {label: __('List', 'upcoming-events-lists'), value: 'list'},
                            {label: __('Grid', 'upcoming-events-lists'), value: 'grid'},
                        ]}
                        onChange={(view_type) => setAttributes({view_type})}
                    />
                </div>
                <ToggleControl
                    label={__('Show all event link buttons.', 'upcoming-events-lists')}
                    checked={show_all_event_link}
                    onChange={() => setAttributes({show_all_event_link: !show_all_event_link})}
                />
            </PanelBody>
            <PanelBody
                title={__('Responsive Settings', 'upcoming-events-lists')}
                initialOpen={false}
            >
                <SelectControl
                    label={__('Columns:Phone', 'upcoming-events-lists')}
                    value={columns_on_phone}
                    onChange={(columns_on_phone) => setAttributes({columns_on_phone})}
                    options={columnsOptions}
                />
                <SelectControl
                    label={__('Columns:Tablet', 'upcoming-events-lists')}
                    value={columns_on_tablet}
                    onChange={(columns_on_tablet) => setAttributes({columns_on_tablet})}
                    options={columnsOptions}
                />
                <SelectControl
                    label={__('Columns:Desktop', 'upcoming-events-lists')}
                    value={columns_on_desktop}
                    onChange={(columns_on_desktop) => setAttributes({columns_on_desktop})}
                    options={columnsOptions}
                />
                <SelectControl
                    label={__('Columns:Widescreen', 'upcoming-events-lists')}
                    value={columns_on_widescreen}
                    onChange={(columns_on_widescreen) => setAttributes({columns_on_widescreen})}
                    options={columnsOptions}
                />
            </PanelBody>
        </InspectorControls>
    )
    return (
        <div {...useBlockProps()}>
            {InspectorControlsEl}
            <ServerSideRender
                block="upcoming-events-lists/events"
                attributes={attributes}
            />
        </div>
    );
}