/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import {registerBlockType} from '@wordpress/blocks';
import {__} from '@wordpress/i18n';
import {
  TextControl,
  Button,
  SelectControl,
  ToggleControl,
  FontSizePicker,
  ColorIndicator,
  Dropdown,
  DropdownContentWrapper,
  ColorPalette,
  Flex,
  FlexBlock,
  __experimentalGrid as Grid,
  __experimentalToggleGroupControl as ToggleGroupControl,
  __experimentalToggleGroupControlOption as ToggleGroupControlOption
} from '@wordpress/components';

import {
  __experimentalBoxControl as BoxControl,
  __experimentalToolsPanel as ToolsPanel,
  __experimentalToolsPanelItem as ToolsPanelItem,
  __experimentalUnitControl as UnitControl,
} from '@wordpress/components';

import {Panel, PanelBody, PanelRow} from '@wordpress/components';
import {useState} from '@wordpress/element';


/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
  useBlockProps,
  InspectorControls,
} from '@wordpress/block-editor';

const LatepointBookFormWrapper = styled.div`
  display: flex;
  justify-content: space-around;
`;

const LatepointBookForm = styled.div`
  display: flex;
  box-shadow: 0px 2px 4px -1px rgba(0,0,0,0.1);
  border-radius: 4px;
  border: 1px solid #ddd;
  border-bottom-color: #bbb;
  background-color: #fff;
  flex: 0;
`;

const SidePanel = styled.div`
  flex: 0 0 180px;
  width: 180px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 40px;
  border-right: 1px solid #eee;
`;

const StepSideImage = styled.div`
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #eee;
  margin: 0px auto;
`;

const StepSideContent = styled.div`
`;

const StepSideName = styled.div`
  padding: 10px;
  border-radius: 4px;
  background-color: #eee;
  margin-bottom: 10px;
`;

const StepSideDescription = styled.div`
  padding: 20px;
  border-radius: 4px;
  background-color: #f8f8f8;
  margin-bottom: 20px;
`;

const StepSideExtra = styled.div`
  padding: 10px;
  border-radius: 4px;
  background-color: #f8f8f8;
`;


const MainPanel = styled.div`
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  max-width: 400px;
`;
const StepMainName = styled.div`
  padding: 10px;
  border-radius: 4px;
  background-color: #eee;
`;
const StepContent = styled.div`
  display: flex;
  gap: 20px;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #eee;
  margin-bottom: auto;
`;

const StepContentTile = styled.div`
  padding: 40px;
  border-radius: 4px;
  background-color: #f8f8f8;
  flex: 1;
`;

const StepButtons = styled.div`
  margin-top: auto;
  border-top: 1px solid #eee;
  padding-top: 15px;
  margin-top: 15px;
  display: flex;
  justify-content: space-between;
`;

const StepButtonPrev = styled.div`
  padding: 10px 25px;
  background-color: #eee;
  border-radius: 4px;
`;

const StepButtonNext = styled.div`
  padding: 10px 25px;
  background-color: #b4c6f5;
  border-radius: 4px;
`;

const SummaryPanel = styled.div`
  flex: 0 0 180px;
  width: 180px;
  border-left: 1px solid #eee;
  padding: 20px;
  display: flex;
  flex-direction: column;
`;

const SummaryHeading = styled.div`
  padding: 10px;
  border-radius: 4px;
  background-color: #eee;
`;

const SummaryContent = styled.div`
  border-top: 1px solid #eee;
  margin-top: 15px;
  padding-top: 15px;
`;
const SummaryTile = styled.div`
  padding: 5px;
  border-radius: 4px;
  background-color: #f8f8f8;
  margin-bottom: 10px;
`;
const SummaryFoot = styled.div`
  margin-top: auto;
  border-top: 1px solid #eee;
  padding-top: 15px;
`;
const SummaryTotal = styled.div`
  padding: 10px;
  border-radius: 4px;
  background-color: #eee;
`;


const ColorAttributesWrapper = styled.div`
  margin-bottom: 15px;
  border: 1px solid #eee;
`;

const PanelRowBlock = styled(PanelRow)`
  display:block;
  margin-bottom: 20px;
`;

const SingleColumnItem = styled(ToolsPanelItem)`
    grid-column: span 1;
`;


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({attributes, setAttributes}) {
  const blockProps = useBlockProps();

  const colors = [
    {
      "name": "Black",
      "color": "#000000"
    },
    {
      "name": "White",
      "color": "#ffffff"
    },
    {
      "name": "Blue",
      "color": "#5376ea"
    }
  ]

  const renderTextColorPicker = () => (
    <ColorPalette
      value={attributes.text_color}
      colors={colors}
      onChange={(color) => setAttributes({text_color: color})}
    />
  );

  const renderBgColorPicker = () => (
    <ColorPalette
      value={attributes.bg_color}
      colors={colors}
      onChange={(color) => setAttributes({bg_color: color})}
    />
  );

  const generateStyles = () => {
    let styles = {}
    if (attributes.border_radius) styles.borderRadius = attributes.border_radius
    if (attributes.bg_color) styles.backgroundColor = attributes.bg_color
    if (attributes.text_color) styles.color = attributes.text_color
    if (attributes.font_size) styles.fontSize = attributes.font_size
    return styles
  }


  return (
    <div {...blockProps}>
      <InspectorControls>
        <Panel>
          <PanelBody title="Booking Form Settings">
            <PanelRow>
              <ToggleControl
                label="Hide Summary Panel"
                checked={attributes.hide_summary}
                onChange={(value) => setAttributes({hide_summary: value})}
              />
            </PanelRow>
            <PanelRow>
              <ToggleControl
                label="Hide Side Panel"
                checked={attributes.hide_side_panel}
                onChange={(value) => setAttributes({hide_side_panel: value})}
              />
            </PanelRow>
          </PanelBody>
        </Panel>
        <Panel>
          <PanelBody title="Step Settings" initialOpen={false}>
            <PanelRowBlock>
              <SelectControl
                label={__('Preselected Agent', 'latepoint')}
                value={attributes.selected_agent}
                onChange={(value) => setAttributes({selected_agent: value})}
                options={latepoint_helper.selected_agents_options}
              />
            </PanelRowBlock>
            <PanelRowBlock>
              <SelectControl
                label={__('Preselected Service', 'latepoint')}
                value={attributes.selected_service}
                onChange={(value) => setAttributes({selected_service: value})}
                options={latepoint_helper.selected_services_options}
              />
            </PanelRowBlock>
            <PanelRowBlock>
              <SelectControl
                label={__('Preselected Service Category', 'latepoint')}
                value={attributes.selected_service_category}
                onChange={(value) => setAttributes({selected_service_category: value})}
                options={latepoint_helper.selected_service_categories_options}
              />
            </PanelRowBlock>
            <PanelRowBlock>
              <SelectControl
                label={__('Preselected Location', 'latepoint')}
                value={attributes.selected_location}
                onChange={(value) => setAttributes({selected_location: value})}
                options={latepoint_helper.selected_locations_options}
              />
            </PanelRowBlock>
            <PanelRow>
              <TextControl
                label={__('Preselected Booking Start Date', 'latepoint')}
                value={attributes.selected_start_date || ''}
                placeholder="YYYY-MM-DD"
                onChange={(value) => setAttributes({selected_start_date: value})}
              />
            </PanelRow>
            <PanelRow>
              <TextControl
                label={__('Preselected Booking Start Time', 'latepoint')}
                value={attributes.selected_start_time || ''}
                placeholder="Minutes"
                onChange={(value) => setAttributes({selected_start_time: value})}
              />
            </PanelRow>
            <PanelRow>
              <TextControl
                label={__('Preselected Duration', 'latepoint')}
                value={attributes.selected_duration || ''}
                placeholder="Minutes"
                onChange={(value) => setAttributes({selected_duration: value})}
              />
            </PanelRow>
            <PanelRow>
              <TextControl
                label={__('Preselected Total Attendees', 'latepoint')}
                value={attributes.selected_total_attendees || ''}
                placeholder="Number"
                onChange={(value) => setAttributes({selected_total_attendees: value})}
              />
            </PanelRow>
          </PanelBody>
        </Panel>
        <Panel>
          <PanelBody title="Other Settings" initialOpen={false}>
            <PanelRow>
              <TextControl
                label="Source ID"
                value={attributes.source_id || ''}
                onChange={(value) => setAttributes({source_id: value})}
              />
            </PanelRow>
            <PanelRow>
              <TextControl
                label="Calendar Start Date"
                value={attributes.calendar_start_date || ''}
                placeholder="YYYY-MM-DD"
                onChange={(value) => setAttributes({calendar_start_date: value})}
              />
            </PanelRow>
            <PanelRow>
              <TextControl
                label="Show Services"
                placeholder="Comma separated service IDs"
                value={attributes.show_services || ''}
                onChange={(value) => setAttributes({show_services: value})}
              />
            </PanelRow>
            <PanelRow>
              <TextControl
                label="Show Service Categories"
                placeholder="Comma separated category IDs"
                value={attributes.show_service_categories || ''}
                onChange={(value) => setAttributes({show_service_categories: value})}
              />
            </PanelRow>
            <PanelRow>
              <TextControl
                label="Show Agents"
                placeholder="Comma separated agent IDs"
                value={attributes.show_agents || ''}
                onChange={(value) => setAttributes({show_agents: value})}
              />
            </PanelRow>
            <PanelRow>
              <TextControl
                label="Show Locations"
                placeholder="Comma separated location IDs"
                value={attributes.show_locations || ''}
                onChange={(value) => setAttributes({show_locations: value})}
              />
            </PanelRow>
          </PanelBody>
        </Panel>
      </InspectorControls>
      <LatepointBookFormWrapper>
        <LatepointBookForm style={generateStyles()}>
          {!attributes.hide_side_panel && <SidePanel>
            <StepSideImage></StepSideImage>
            <StepSideContent>
              <StepSideName></StepSideName>
              <StepSideDescription></StepSideDescription>
            </StepSideContent>
            <StepSideExtra></StepSideExtra>
          </SidePanel>}
          <MainPanel>
            <StepMainName></StepMainName>
            <StepContent>
              <StepContentTile></StepContentTile>
              <StepContentTile></StepContentTile>
              <StepContentTile></StepContentTile>
            </StepContent>
            <StepButtons>
              <StepButtonPrev></StepButtonPrev>
              <StepButtonNext></StepButtonNext>
            </StepButtons>
          </MainPanel>
          {!attributes.hide_summary && <SummaryPanel>
            <SummaryHeading></SummaryHeading>
            <SummaryContent>
              <SummaryTile></SummaryTile>
              <SummaryTile></SummaryTile>
              <SummaryTile></SummaryTile>
              <SummaryTile></SummaryTile>
            </SummaryContent>
            <SummaryFoot>
              <SummaryTotal></SummaryTotal>
            </SummaryFoot>
          </SummaryPanel>}
        </LatepointBookForm>
      </LatepointBookFormWrapper>
    </div>
  );
}
