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

0.9.5.5xx Notes

. Added receipt staging functionality
. Added Excel import for contractor receipts
. Added Excel export for remittance template

0.9.5.101 Notes

. Revised login layout and last login processing
. Added member IUPAT ID, email, in-application processing and dues delinquency checks 
. Added validation checking for application date, birth date (age), 
. Revised cosmetic to member transaction windows
. Added member accounting framework, injected dates for testing
. Added status handling for new entries
. Fixed minor bugs

0.9.4.331 Notes

. Added conditional Close Date to Create Project Screen
. Added range hours and amounts for LMA maintenance projects

0.9.4.325 Notes

. Revised user models names to match standard of other models
. Revised config files to formalize production/development environments

0.9.4.321 Notes

. Enabled cookie-based user identity
. Revised project disposition denied to force close date
. Fixed crash caused when opening LMA document
. Added total JTP hold amount on index grid
. Revised 1st address data entry to hide type (defaulted as mailing)
. Fixed crash caused by zero JTP registrations on a project 

0.9.4.311 Notes

. Simplified signature for type-specific project controllers
. Revised Special Projects summary order by to project name
. Revised contractor's Special Projects summary to allow show-all option toggle

0.9.4.301 Notes

. Added focus on current menu item in sidenav on admin page
. Improved member accordion status, class & employer title access (removed static "current" row functions) 
. Revised member general search: removed member ID and island; added employer and sortable class; status is sortable
. Revised government ID to mask display in index and view record
. Added quick search by name for members, contractors
. Added ability to enter signatory on contractor create screen
. Revised member address types: 'M' Mailing (default in create), 'L' Location
. Revised contractor address types: 'M' Mailing (default in create), 'L' Location, 'O' Other
. Revised address lines 1 and 2 to accept 50 characters

0.9.4.201 Notes

. Cleaned up member search
. Added island to sortable member index columns
. Added trade specialties to membership
. Added document filing features to membership

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