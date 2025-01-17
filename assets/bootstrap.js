// import { startStimulusApp } from '@symfony/stimulus-bundle';

// const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
import { Application } from 'stimulus';
import { definitionsFromContext } from 'stimulus/webpack-helpers';

// Initialisation de Stimulus
const application = Application.start();

// Importation automatique des contrôleurs Stimulus à partir du dossier controllers
const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));
