import {
    LiteGraph,
    LGraph,
    LGraphNode,
    LGraphCanvas
} from './Contrib/Litegraph/litegraph.es.js';

// Developer Info: Beta mirgation to litegraph.es.js

// ============================================================
// LiteGraph
// ============================================================

LiteGraph.clearRegisteredTypes();


// ============================================================
// Extension Node
// ============================================================

class ExtensionNode extends LGraphNode {

    constructor() {
        super();

        this.title = 'Extension';

        this.size = [280, 120];

        this.properties = {
            extensionKey: 'my_extension',
            vendorName: 'MyVendor',
            extensionName: 'MyExtension',
            version: '0.0.1'
        };

        this.addOutput(
            'components',
            'Extension'
        );
    }
}


// ============================================================
// Component Node
// ============================================================

class ComponentNode extends LGraphNode {

    constructor() {
        super();

        this.title = 'Component';

        this.size = [260, 120];

        this.properties = {
            componentKey: '',
            componentName: ''
        };

        this.addInput(
            'extension',
            'Extension'
        );

        this.addOutput(
            'properties',
            'Component'
        );
    }
}


// ============================================================
// Property Node
// ============================================================

class PropertyNode extends LGraphNode {

    constructor() {
        super();

        this.title = 'Property';

        this.size = [240, 110];

        this.properties = {
            propertyKey: '',
            propertyName: ''
        };

        this.addInput(
            'component',
            'Component'
        );

        this.addOutput(
            'fields',
            'Property'
        );
    }
}


// ============================================================
// Field Node
// ============================================================

class FieldNode extends LGraphNode {

    constructor() {
        super();

        this.title = 'Field';

        this.size = [220, 90];

        this.properties = {
            fieldKey: '',
            fieldName: ''
        };

        this.addInput(
            'property',
            'Property'
        );
    }
}


// ============================================================
// Node Types registrieren
// ============================================================

LiteGraph.registerNodeType(
    'Extension',
    ExtensionNode
);

LiteGraph.registerNodeType(
    'Component',
    ComponentNode
);

LiteGraph.registerNodeType(
    'Property',
    PropertyNode
);

LiteGraph.registerNodeType(
    'Field',
    FieldNode
);


// ============================================================
// Container
// ============================================================

const container = document.getElementById(
    'graph-container'
);


if (!container) {

    console.error(
        'LiteGraph: #graph-container nicht gefunden'
    );

} else {


    // ========================================================
    // Canvas
    // ========================================================

    const canvas = document.createElement(
        'canvas'
    );

    canvas.style.display = 'block';
    canvas.style.width = '100%';
    canvas.style.height = '100%';

    container.appendChild(
        canvas
    );


    // ========================================================
    // Canvas Größe
    // ========================================================

    function resizeCanvasElement() {

        const width =
            container.clientWidth || 1000;

        const height =
            container.clientHeight || 800;

        canvas.width = width * 2;
        canvas.height = height* 2;


    }


    resizeCanvasElement();


    // ========================================================
    // Graph
    // ========================================================

    const graph = new LGraph();


    // ========================================================
    // Graph Canvas
    // ========================================================

    const graphCanvas =
        new LGraphCanvas(
            canvas,
            graph
        );


    // ========================================================
    // Daten aus HTML lesen
    // ========================================================

    let extension = {};
    let components = {};


    try {

        extension = JSON.parse(
            container.dataset.extension || '{}'
        );

    } catch (error) {

        console.error(
            'LiteGraph: Extension JSON ungültig',
            error
        );
    }


    try {

        components = JSON.parse(
            container.dataset.components || '{}'
        );

    } catch (error) {

        console.error(
            'LiteGraph: Components JSON ungültig',
            error
        );
    }


    // ========================================================
    // Layout-Konfiguration
    // ========================================================

    const layout = {

        extensionX: 100,

        componentX: 500,

        propertyX: 900,

        fieldX: 1300,

        startY: 100,

        componentGap: 100,

        propertyGap: 40,

        fieldGap: 30,

        nodeHeight: {

            extension: 120,

            component: 120,

            property: 110,

            field: 90
        }
    };


    // ========================================================
    // Extension Node
    // ========================================================

    const extensionNode =
        LiteGraph.createNode(
            'Extension'
        );


    if (!extensionNode) {

        console.error(
            'LiteGraph: Extension Node konnte nicht erstellt werden'
        );

    } else {

        extensionNode.pos = [
            layout.extensionX,
            layout.startY
        ];


        // ----------------------------------------------------
        // Extension Eigenschaften
        // ----------------------------------------------------

        extensionNode.properties.extensionKey =
            extension.extensionKey ||
            'my_extension';

        extensionNode.properties.vendorName =
            extension.vendorName ||
            'MyVendor';

        extensionNode.properties.extensionName =
            extension.extensionName ||
            'MyExtension';

        extensionNode.properties.version =
            extension.version ||
            '0.0.1';


        extensionNode.title =
            extension.extensionName ||
            'Extension';


        graph.add(
            extensionNode
        );


        // ====================================================
        // Komponenten
        // ====================================================

        let currentY =
            layout.startY;


        Object.keys(
            components
        ).forEach(
            (componentKey) => {

                const componentGroup =
                    components[componentKey];


                if (
                    !componentGroup ||
                    typeof componentGroup !== 'object'
                ) {
                    return;
                }


                Object.keys(
                    componentGroup
                ).forEach(
                    (componentNameKey) => {

                        const componentData =
                            componentGroup[
                                componentNameKey
                            ];


                        console.log(
                            'Component:',
                            componentKey,
                            componentNameKey
                        );


                        // ====================================================
                        // Component Node
                        // ====================================================

                        const componentNode =
                            LiteGraph.createNode(
                                'Component'
                            );


                        if (!componentNode) {

                            console.error(
                                'LiteGraph: Component Node konnte nicht erstellt werden'
                            );

                            return;
                        }


                        componentNode.title =
                            componentNameKey;


                        componentNode.properties.componentKey =
                            componentKey;


                        componentNode.properties.componentName =
                            componentNameKey;


                        componentNode.pos = [
                            layout.componentX,
                            currentY
                        ];


                        graph.add(
                            componentNode
                        );


                        // ====================================================
                        // Extension → Component
                        // ====================================================

                        extensionNode.connect(
                            0,
                            componentNode,
                            0
                        );


                        console.log(
                            'Connection: Extension → Component',
                            componentNameKey
                        );


                        // ====================================================
                        // Properties
                        // ====================================================

                        let propertyY =
                            currentY;


                        let propertyCount =
                            0;


                        if (
                            componentData &&
                            typeof componentData === 'object'
                        ) {

                            Object.keys(
                                componentData
                            ).forEach(
                                (propertyKey) => {

                                    const propertyData =
                                        componentData[
                                            propertyKey
                                        ];


                                    if (
                                        !propertyData ||
                                        typeof propertyData !== 'object'
                                    ) {
                                        return;
                                    }


                                    Object.keys(
                                        propertyData
                                    ).forEach(
                                        (propertyNameKey) => {

                                            console.log(
                                                'Property:',
                                                componentKey,
                                                componentNameKey,
                                                propertyKey,
                                                propertyNameKey
                                            );


                                            // ========================================
                                            // Property Node
                                            // ========================================

                                            const propertyNode =
                                                LiteGraph.createNode(
                                                    'Property'
                                                );


                                            if (!propertyNode) {

                                                console.error(
                                                    'LiteGraph: Property Node konnte nicht erstellt werden'
                                                );

                                                return;
                                            }


                                            propertyNode.title =
                                                propertyNameKey;


                                            propertyNode.properties.propertyKey =
                                                propertyKey;


                                            propertyNode.properties.propertyName =
                                                propertyNameKey;


                                            propertyNode.pos = [
                                                layout.propertyX,
                                                propertyY
                                            ];


                                            graph.add(
                                                propertyNode
                                            );


                                            // ========================================
                                            // Component → Property
                                            // ========================================

                                            componentNode.connect(
                                                0,
                                                propertyNode,
                                                0
                                            );


                                            console.log(
                                                'Connection: Component → Property',
                                                propertyNameKey
                                            );


                                            // ========================================
                                            // Fields
                                            // ========================================

                                            const fields =
                                                propertyData[
                                                    propertyNameKey
                                                ];


                                            let fieldY =
                                                propertyY;


                                            let fieldCount =
                                                0;


                                            if (
                                                fields &&
                                                typeof fields === 'object'
                                            ) {

                                                Object.keys(
                                                    fields
                                                ).forEach(
                                                    (fieldKey) => {

                                                        console.log(
                                                            'Field:',
                                                            componentKey,
                                                            componentNameKey,
                                                            propertyKey,
                                                            propertyNameKey,
                                                            fieldKey
                                                        );


                                                        // ====================================
                                                        // Field Node
                                                        // ====================================

                                                        const fieldNode =
                                                            LiteGraph.createNode(
                                                                'Field'
                                                            );


                                                        if (!fieldNode) {

                                                            console.error(
                                                                'LiteGraph: Field Node konnte nicht erstellt werden'
                                                            );

                                                            return;
                                                        }


                                                        fieldNode.title =
                                                            fieldKey;


                                                        fieldNode.properties.fieldKey =
                                                            fieldKey;


                                                        fieldNode.properties.fieldName =
                                                            fields[
                                                                fieldKey
                                                            ];


                                                        fieldNode.pos = [
                                                            layout.fieldX,
                                                            fieldY
                                                        ];


                                                        graph.add(
                                                            fieldNode
                                                        );


                                                        // ====================================
                                                        // Property → Field
                                                        // ====================================

                                                        propertyNode.connect(
                                                            0,
                                                            fieldNode,
                                                            0
                                                        );


                                                        console.log(
                                                            'Connection: Property → Field',
                                                            fieldKey
                                                        );


                                                        fieldY +=
                                                            layout.nodeHeight.field +
                                                            layout.fieldGap;


                                                        fieldCount++;
                                                    }
                                                );
                                            }


                                            // ========================================
                                            // Höhe der Property
                                            // ========================================

                                            const fieldHeight =
                                                fieldCount > 0

                                                    ? (
                                                        fieldCount *
                                                        (
                                                            layout.nodeHeight.field +
                                                            layout.fieldGap
                                                        )
                                                    )

                                                    : layout.nodeHeight.property;


                                            const propertyHeight =
                                                Math.max(
                                                    layout.nodeHeight.property,
                                                    fieldHeight
                                                );


                                            propertyY +=
                                                propertyHeight +
                                                layout.propertyGap;


                                            propertyCount++;
                                        }
                                    );
                                }
                            );
                        }


                        // ====================================================
                        // Höhe des Component-Blocks
                        // ====================================================

                        const componentHeight =
                            Math.max(
                                layout.nodeHeight.component,
                                propertyY - currentY
                            );


                        // ====================================================
                        // Nächsten Component positionieren
                        // ====================================================

                        currentY +=
                            componentHeight +
                            layout.componentGap;
                    }
                );
            }
        );
    }


    // ========================================================
    // Graph starten
    // ========================================================

    graph.start();


    // ========================================================
    // Interaktion
    // ========================================================

    graphCanvas.allow_interaction =
        true;

    graphCanvas.allow_dragnodes =
        true;


    // ========================================================
    // Zeichnen
    // ========================================================

    graphCanvas.draw(
        true,
        true
    );


    // ========================================================
    // Debug
    // ========================================================

    console.log(
        'LiteGraph: Graph',
        graph
    );

    console.log(
        'LiteGraph: Nodes',
        graph._nodes
    );

    console.log(
        'LiteGraph: Anzahl Nodes',
        graph._nodes.length
    );


    // ========================================================
    // Resize
    // ========================================================

    window.addEventListener(
        'resize',
        () => {

            resizeCanvasElement();

            graphCanvas.resize();

            graphCanvas.draw(
                true,
                true
            );
        }
    );
}