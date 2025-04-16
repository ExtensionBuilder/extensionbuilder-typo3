LiteGraph.clearRegisteredTypes();

class Extension extends LiteGraph.LGraphNode {

    constructor() {
        super();

        this.title = "Extension";

// extConfTemplate

        this.properties = {
            extensionKey: "my_extension",
            vendorName: "MyVendor",
            extensionName: "MyExtension",
            composerName: "",
            title: "My Extension",
            description: "With this extension you can...",
            version: "0.0.1",
        };
    }
}
LiteGraph.registerNodeType("Extension", Extension);


class Components extends LiteGraph.LGraphNode {
    constructor() {
        super();

        this.title = "Components";
        this.size[0] = 400;

        this.addInput("extension", "Components");
    }
}
LiteGraph.registerNodeType("Components", Components);

class Component extends LiteGraph.LGraphNode {
    constructor() {
        super();

        this.title = "Component";

        this.addInput("components", "Component");
    }
}
LiteGraph.registerNodeType("Component", Component);

class ComponentObj extends LiteGraph.LGraphNode {
    constructor() {
        super();

        this.title = "ComponentObj";

        this.addInput("component", "ComponentObj");
    }
}
LiteGraph.registerNodeType("ComponentObj", ComponentObj);



class Property extends LiteGraph.LGraphNode {
    constructor() {
        super();

        this.title = "Property";
        this.size[0] = 400;

        this.addInput("propertys", "Property");
    }
}
LiteGraph.registerNodeType("Property", Property);

class PropertyObj extends LiteGraph.LGraphNode {
    constructor() {
        super();

        this.title = "PropertyObj";

        this.addInput("property", "PropertyObj");
    }
}
LiteGraph.registerNodeType("PropertyObj", PropertyObj);



class Plugins extends LiteGraph.LGraphNode {
    constructor() {
        super();

        this.title = "Plugins";

        this.addInput("extension", "Plugins");
    }
}
LiteGraph.registerNodeType("Plugins", Plugins);


class Backends extends LiteGraph.LGraphNode {
    constructor() {
        super();

        this.title = "Backends";

        this.addInput("extension", "Backends");
    }
}
LiteGraph.registerNodeType("Backends", Backends);



class Tables extends LiteGraph.LGraphNode {
    constructor() {
        super();

        this.title = "Tables";

        this.addInput("extension", "Tables");
    }
}
LiteGraph.registerNodeType("Tables", Tables);


class Miscs extends LiteGraph.LGraphNode {
    constructor() {
        super();

        this.title = "Miscs";

        this.addInput("extension", "Miscs");
    }
}
LiteGraph.registerNodeType("Miscs", Miscs);




function buildNodes( nodeX, nodeY, data ) {

    return nodeY;
}

// 9999



// Container auswählen
const container = document.getElementById("graph-container");

// Canvas erstellen
const canvas = document.createElement("canvas");
container.appendChild(canvas);

// Litegraph Setup
const graph = new LGraph();
const graphCanvas = new LGraphCanvas(canvas, graph);

// Automatic resizing
function resizeCanvas() {
    canvas.width = container.clientWidth;
    canvas.height = container.clientHeight;
    graphCanvas.resize();
}

// Event-Listener for window size
window.addEventListener("resize", resizeCanvas);
resizeCanvas();// Init


// ToDo
//graph.onNodeAdded = function (node) {
//    // Show error if node of type Extension was added twice
//    if (node.type === "TYPO3/Extension") {
//        for (let i in graph._nodes) {
//            let existingNode = graph._nodes[i];
//
//            // Wenn ein anderer Knoten vom Typ "TYPO3/Extension" gefunden wird
//            if (existingNode !== node && existingNode.type === "TYPO3/Extension") {
//                // Entferne den neu hinzugefügten Knoten
//                graph.remove(node);
//
//                // Gebe eine Nachricht an den Benutzer aus
//                alert("Only one node of type TYPO3/Extension is allowed!");
//
//                // Beende die Funktion
//                return;
//            }
//        }
//    }
//}

    

const extension = JSON.parse(container.dataset.extension);
const components = JSON.parse(container.dataset.components);
//const tables = JSON.parse(container.dataset.tables);
// bakend
// plugin
// controller

//console.log(extension);
//console.log(extension.extensionName);

//console.log(Object.keys(components).length);
//console.log(components['viewHelpers']['arg1']);


let extensionX = 10;
let extensionY = 40;

let componentsX = 310;
let componentsY = 40;
let componentsYadd = 65;

let componentX = 610;
let componentY = 40;
let componentYadd = 65;
let componentYoffset = 0;

let componentObjX = 910;
let componentObjY = 40;
let componentObjYadd = 65;
let componentObjYoffset = 0;

let propertyX = 1210;
let propertyY = 40;
let propertyYadd = 65;
let propertyYoffset = 0;

let propertyObjX = 1510;
let propertyObjY = 40;
let propertyObjYadd = 65;
let propertyObjYoffset = 0;

let nodeExtension = LiteGraph.createNode(
        'Extension', 'Extension', {
            pos: [extensionX, extensionY]
        }
);
graph.add(nodeExtension);

let miscs = 99;
let wert = (typeof miscs !== "undefined") ? miscs : false;

console.log( "buildNodes "+buildNodes( 11, wert, 22 ));

//console.log(components[element]);

if (Object.keys(components).length > 0) {

    let nodeComponents = LiteGraph.createNode(
        'Components', 'Extension', {
            pos: [componentsX, componentsY ]
        }
    );
    graph.add(nodeComponents);

    nodeExtension.addOutput("components", "Components");
    nodeExtension.connect('components', nodeComponents, 0);


    Object.keys(components).forEach(
        (component) =>  {
            let componentName = component.charAt(0).toUpperCase() + component.slice(1);

            let nodeComponent = LiteGraph.createNode(
                'Component', 'Component/' + componentName, {
                    pos: [componentX, componentY + componentYoffset],
                    title: componentName
                }
            );
            graph.add(nodeComponent);

            nodeComponents.addOutput(component, "Component");
            nodeComponents.connect(component, nodeComponent, 0);

            componentY += componentYadd;
            componentYoffset = 0;

            Object.keys(components[component]).forEach(
                (componentObj) =>  {
                    let componentObjName = componentObj.charAt(0).toUpperCase() + componentObj.slice(1);

                    let nodeComponentObj = LiteGraph.createNode(
                        'ComponentObj', 'Component/ComponentObj/' + componentObjName, {
                            pos: [componentObjX, componentObjY],
                            title: componentObjName
                        }
                    );
                    graph.add(nodeComponentObj);

                    nodeComponent.addOutput(componentObj, "ComponentObj");
                    nodeComponent.connect(componentObj, nodeComponentObj, 0);

                    if (components[component][componentObj]['propertys']) {

                        propertyY = componentObjY; // OK

                        Object.keys(components[component][componentObj]['propertys']).forEach(
                            (property) =>  {
                                let propertyName = property.charAt(0).toUpperCase() + property.slice(1);

                                let nodeProperty = LiteGraph.createNode(
                                    'Property', 'Component/ComponentObj/Property' + propertyName, {
                                        pos: [ propertyX,  propertyY],
                                        title:  propertyName
                                    }
                                );
                                graph.add(nodeProperty);

                                nodeComponentObj.addOutput(property, "Property");
                                nodeComponentObj.connect(property, nodeProperty, 0);

                                propertyObjY = componentObjY; // OK
                                propertyYoffset = 0;
                                Object.keys(components[component][componentObj]['propertys'][property]).forEach(
                                    (propertyObj) =>  {
                                        let propertyObjName = propertyObj.charAt(0).toUpperCase() + propertyObj.slice(1);

                                        let nodePropertyObj = LiteGraph.createNode(
                                            'PropertyObj', 'Component/ComponentObj/PropertyObj' + propertyObjName, {
                                                pos: [ propertyObjX,  propertyObjY],
                                                title:  propertyObjName
                                            }
                                        );
                                        graph.add(nodePropertyObj);

                                        if (components[component][componentObj]['propertys'][property][propertyObj]['fields']) {
                                            Object.keys(components[component][componentObj]['propertys'][property][propertyObj]['fields']).forEach(
                                                (field) =>  {

nodePropertyObj.addProperty("meinText", components[component][componentObj]['propertys'][property][propertyObj]['fields'][field]);

console.log(components[component][componentObj]['propertys'][property][propertyObj]['fields'][field]);

                                                    nodePropertyObj.addWidget(
                                                        "text",
                                                        field,
                                                        nodePropertyObj.properties.meinText,
                                                        (v) => {
                                                            nodePropertyObj.properties.meinText = v;
                                                        }
                                                    );
                                                    propertyObjY += 30;
                                                }
                                            );
                                        }

                                        nodeProperty.addOutput(propertyObj, "PropertyObj");
                                        nodeProperty.connect(propertyObj, nodePropertyObj, 0);

                                        propertyObjY += propertyObjYadd;

                                        propertyYoffset += 13;

                                    }
                                );
                                componentObjY = propertyObjY - propertyObjYadd + 5;
                                console.log(propertyYoffset);
                                propertyY += propertyYadd + propertyYoffset;
                            }
                        );
                    }
                    componentObjY += componentObjYadd;
                }
            );
            componentObjY += 8;
            componentY = componentObjY;
		}
    );

}
//  console.log(components[element]);



//        this.addOutput("plugins", "Plugins");
//        this.addOutput("backends", "Backends");
//        this.addOutput("tables", "Tables");

//if (miscs) {
//    if (Object.keys(miscs).length > 0) {
        let nodeMiscs = LiteGraph.createNode(
            'Miscs', 'Extension', {
                pos: [componentsX, componentY ]
            }
        );
        graph.add(nodeMiscs);
        nodeExtension.addOutput("miscs", "Miscs");
        nodeExtension.connect('miscs', nodeMiscs, 0);
//    }
//}


graphCanvas.allow_interaction = false;
graphCanvas.allow_dragnodes = false;
graphCanvas.allow_searchbox = false;
graphCanvas.allow_menu = false;

graph.start();

