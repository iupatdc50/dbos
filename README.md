# dbos
DC50 Business Office Support 

Private system to manage back office operations at DC50.  

Main application covers:
. General membership management
. Contractors and employment management
. Special project tracking

Modules are setup for:
. Admin, which handles settings, RBAC and other house-keeping
. Accounting
. Training

0.9.4 Notes

. Contractor status is now automatically set, based on the existence of active signatory
. Added retail market agreement as an ancillary
. Added project labor agreement as an option on the signatory
. PDCA is now called "Association Member" and is specific to trade agreements
. CBA was removed
. Added IUPAT membership ID

0.9.3 Notes

. Added active/inactive status to contractor. Index display defaults to active only. 
. Modified member to allow search on full name and display full name in index 
. Removed views/employment/_form. (create and loan stand alone.)
. Special projects under a contractor are active and awarded only. 
. Current employee list can now be searched