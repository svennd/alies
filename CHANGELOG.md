# version : 26/07/2020
- added lock/remove event
- added version file
- added support for 30/90 day updated clients export
- added changelog.md
- add stock has been reworked to have a better UI
- fixed update last_bill not getting updated
- fixed an import issue with IDPRAK being case and we checked case insensitive
- tooth now is more modular, and includes cat & horse …
- improved tooth fiche UI
- modified stock to have a state
- removed the link to edit prices for vets
- fixed a bug where moved products would not get the right state
- separated lost from active animals
- finished rework on stock UI
- fix death/gone pets can't have fiche/consults
- add stock detail spread
- lost pets don't need to be shown as other pets
- invoice overview now shows clients last name
- pets can now be search with a like
- events can now be locked only when no report is written
- birthday is now visible on event page (for vaccines)
- slightly reworked add pet to show what is required
- added last bill to pet name search
- added product name to title when in edit mode
- changed event report UI
- added last 1m/6m/1y usage to product edit page

# version : 07/08/2020
- migrations version : 6
- added edit price to the events/bill system
- simple reductions added
- add support for noview history
- add support for changing pets from owner
- removed edit client from pets fiche
- moved edit price button so its only visible when prod/proc are added
- added a show no-history function to history (for no-history lookups)
- add report for usage (all products)
- add report for specific stock for product
- add report for specific usage for product
- add index link for reports
- add stock maintenance queries
- add stock state, and only show them (might be incomplete)
- add support for search on client id
- add vaccination report
- fixed small link bug on owner/index

# version : 16/08/2020
- migrations version : 7
- modified ui for lost & gone to take up less space, and no space if = 0
- added support for (sliced) uploading files (office, images, videos) to events;
- small error in migrations 006
- changed sql engine to InnoDB

# version : 27/08/2020
- added caching to charts
- removed client charts

# version : 14/12/2020
- add support for GS-1 DataMatrix scanning (on many veternarien products QR like code)
- header alert on empty set
- adding stock of a non existing product
- added support for Kluwer book keeping (export format : xml)
- fix lotnr (not an int)
- add support for lookup/search per breed
- simplify code
- rough selection of invoices
- improved admin invoice screen (default : 3M, until: today)
- hidden non functional messages & alerts from header
- change search behaviour, only search right side
- fix bug where name of a vet resulted in error
- small fix for owner model : now owners will only list 'active pets' (no dead or lost)
- bugfix : added result check in stock_model
- auto add month date in invoice list
- added support for nutrition advice
- designed create/edit pet page
- wysiwyg editor on report & tooth view