# ILIAS Learning Locations Plugin

With this Plugin you can use the ILIAS Learning Location App, available at:  
- [Google-PlayStore](https://play.google.com/store/apps/details?id=ch.studerraimann.ilias.learnloc)  
- [Apple AppStore](https://itunes.apple.com/ch/app/ilias-lernorte/id1066335052)

Navigate with Augmented Reality to Learning Locations and open ILIAS Modules associated with this Point of Learning (opened in Browser).

### Installation
Start at your ILIAS root directory  
```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject  
cd Customizing/global/plugins/Services/Repository/RepositoryObject
git clone https://github.com/studer-raimann/LearnLoc.git  
chown -R www-data:www-data LearnLoc #ensure that the user of your Webserver has full access to this directory
```  
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.  

### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Soure Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.

### Contact
info@studer-raimann.ch  
https://studer-raimann.ch  
