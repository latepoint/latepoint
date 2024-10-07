import {__} from '@wordpress/i18n';
import {SelectControl} from '@wordpress/components';
import React from "react";
import FontSizeControl from "./FontSizeControl";
import LineHeightControl from "./LineHeightControl";
import LetterSpacingControl from "./LetterSpacingControl";

const TypographyControl = ({attributes, setAttributes, fontSizeAttr}) => {

    const fonts = [
        {label: '', value: ''},
        {label: 'Arial', value: 'Arial, sans-serif'},
        {label: 'Helvetica', value: 'Helvetica, sans-serif'},
        {label: 'Times New Roman', value: '"Times New Roman", Times, serif'},
        {label: 'Georgia', value: 'Georgia, serif'},
        {label: 'Courier New', value: '"Courier New", Courier, monospace'},
        {label: 'Verdana', value: 'Verdana, sans-serif'},
        {label: 'Trebuchet MS', value: '"Trebuchet MS", sans-serif'},
        {label: 'Lucida Sans', value: '"Lucida Sans", sans-serif'},
        {label: 'Tahoma', value: 'Tahoma, sans-serif'},
        {label: 'Palatino Linotype', value: '"Palatino Linotype", "Book Antiqua", Palatino, serif'},
        {label: 'Arial Black', value: '"Arial Black", Gadget, sans-serif'},
        {label: 'Comic Sans MS', value: '"Comic Sans MS", cursive, sans-serif'},
        {label: 'Impact', value: 'Impact, Charcoal, sans-serif'},
        {label: 'Lucida Console', value: '"Lucida Console", Monaco, monospace'},
        {label: 'Garamond', value: 'Garamond, serif'},
        {label: 'Roboto', value: '"Roboto", sans-serif'},
        {label: 'Open Sans', value: '"Open Sans", sans-serif'},
        {label: 'Lato', value: '"Lato", sans-serif'},
        {label: 'Montserrat', value: '"Montserrat", sans-serif'},
        {label: 'Oswald', value: '"Oswald", sans-serif'},
        {label: 'Raleway', value: '"Raleway", sans-serif'},
        {label: 'Merriweather', value: '"Merriweather", serif'},
        {label: 'Ubuntu', value: '"Ubuntu", sans-serif'},
        {label: 'Playfair Display', value: '"Playfair Display", serif'},
        {label: 'Nunito', value: '"Nunito", sans-serif'},
        {label: 'PT Serif', value: '"PT Serif", serif'}
    ];


    return (
        <>
            <SelectControl
                label={__('Font Family', 'latepoint')}
                value={attributes.font_family}
                className="latepoint-control-two-columns"
                options={fonts}
                onChange={(font_family) => setAttributes({font_family})}
            />

            <FontSizeControl attributes={attributes} setAttributes={setAttributes} fontSizeAttr={fontSizeAttr}></FontSizeControl>

            <SelectControl
                label={__('Weight', 'latepoint')}
                className="latepoint-control-two-columns"
                value={attributes.font_weight}
                options={[
                    {label: __('400', 'latepoint'), value: '400'},
                    {label: __('500', 'latepoint'), value: '500'},
                    {label: __('600', 'latepoint'), value: '600'},
                    {label: __('700', 'latepoint'), value: '700'},
                ]}
                onChange={(font_weight) => setAttributes({font_weight})}
            />

            <SelectControl
                label={__('Transform', 'latepoint')}
                value={attributes.text_transform}
                className="latepoint-control-two-columns"
                options={[
                    {label: __('Default', 'latepoint'), value: ''},
                    {label: __('None', 'latepoint'), value: 'none'},
                    {label: __('Uppercase', 'latepoint'), value: 'uppercase'},
                    {label: __('Lowercase', 'latepoint'), value: 'lowercase'},
                    {label: __('Capitalize', 'latepoint'), value: 'capitalize'},
                ]}
                onChange={(text_transform) => setAttributes({text_transform})}
            />

            <LineHeightControl attributes={attributes} setAttributes={setAttributes}></LineHeightControl>
            <LetterSpacingControl attributes={attributes} setAttributes={setAttributes}></LetterSpacingControl>
        </>
    );
};

export default TypographyControl;
