/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import {TextControl, ToggleControl, Panel, PanelBody, PanelRow} from '@wordpress/components';


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
import styled from "@emotion/styled";


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

    const LatepointDashboardWrapper = styled.div`
        box-shadow: 0px 2px 4px -1px rgba(0,0,0,0.1);
        border-radius: 4px;
        border: 1px solid #ddd;
        border-bottom-color: #bbb;
        background-color: #fff;
        padding: 20px;
    `;

    const LatepointDashboardFormCaption = styled.div`
        font-weight: 500;
        margin-bottom: 10px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    `;

    const DItems = styled.div`
        display: flex;
        justify-content: space-between;
        gap: 20px;
    `;

    const LatepointDashboardItem = styled.div`
        display: flex;
        flex-direction: column;
        padding: 20px;
        width: 180px;
        border: 1px solid #ddd;
        background: #fbfbfb;
    `;

    const DIName = styled.div`
        padding: 10px;
        border-radius: 4px;
        background-color: #eee;
    `;
    const DIBody = styled.div`
        padding: 10px 0;
        margin: 10px 0;
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
    `;

    const DIFooter = styled.div`
    `;

    const DIButtonPrev = styled.div`
      padding: 10px;
      width: 30px;  
      background-color: #b4c6f5;
      border-radius: 4px;
    `;

    const DIDescription = styled.div`
        padding: 5px;
        border-radius: 4px;
        background-color: #f8f8f8;
        margin-bottom: 5px;
    `;


  return (
      <div {...blockProps}>
        <InspectorControls>
          <Panel>
            <PanelBody title="Dashboard Settings">
              <TextControl
                  label="Caption"
                  value={attributes.caption || ''}
                  onChange={(value) => setAttributes({caption: value})}
              />
                <ToggleControl
                    label="Hide new appointment button and tab"
                    checked={attributes.hide_new_appointment_ui}
                    onChange={(value) => setAttributes({hide_new_appointment_ui: value})}
                />
            </PanelBody>
          </Panel>
        </InspectorControls>
          <LatepointDashboardWrapper>
              <LatepointDashboardFormCaption>{attributes.caption}</LatepointDashboardFormCaption>
              <DItems>
                  <LatepointDashboardItem>
                      <DIName></DIName>
                      <DIBody>
                          <DIDescription></DIDescription>
                          <DIDescription></DIDescription>
                          <DIDescription></DIDescription>
                      </DIBody>
                      <DIFooter>
                          <DIButtonPrev></DIButtonPrev>
                      </DIFooter>
                  </LatepointDashboardItem>
                  <LatepointDashboardItem>
                      <DIName></DIName>
                      <DIBody>
                          <DIDescription></DIDescription>
                          <DIDescription></DIDescription>
                          <DIDescription></DIDescription>
                      </DIBody>
                      <DIFooter>
                          <DIButtonPrev></DIButtonPrev>
                      </DIFooter>
                  </LatepointDashboardItem>
                  <LatepointDashboardItem>
                      <DIName></DIName>
                      <DIBody>
                          <DIDescription></DIDescription>
                          <DIDescription></DIDescription>
                          <DIDescription></DIDescription>
                      </DIBody>
                      <DIFooter>
                          <DIButtonPrev></DIButtonPrev>
                      </DIFooter>
                  </LatepointDashboardItem>
              </DItems>
          </LatepointDashboardWrapper>
      </div>
  );
}
