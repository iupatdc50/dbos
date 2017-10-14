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

0.9.10.610
. Added filters on receipts list for trade and logged in user
. Revised receipt creation wizards to accommodate trade
. Added provision for an accounting month on receipts
. Modified the cash journals and International selection criteria to use accounting month
. Added a report summarizing receipts by payment method

0.9.10.570
. Updated RBAC to include training roles
. Added the ability to attach a document to an employment record
. Improved "in application" handling
. Fixed bugs in employment create, ISC handling

0.9.10.550
. Added "In Service Card" handling
. Cleaned out some dead code

0.9.10.500
. Added reset paid thru when dues receipt deleted
. Added paid thru date on member dues receipts
. Added ability to add fee type columns to contractor receipt in progress
. Added member status to contractor view employee list
. Added ability to add inactive members to contractor receipt
. Added ability to adjust total receipt amount in balance
. Fixed bugs in editable receipt money fields 

0.9.10.100
. Added site calendar to home page with event creator
. Added navigation function to employment & status history panels
. Fixed bugs in time classes, pop-ups, provider projects panel 

0.9.9.100

. Added receipts journal reports
. Added International reports
. Added auto-action support reports
. Added some navigation improvements
. Added some reporting code optimization
. Added new rate class for GF life members ($5 rate)
. Fixed minor bugs

0.9.8.350

. Fixed accounting RBAC hierarchy
. Added dues status & delinquent reports
. Added hardcopy PAC reporting (.331)
. Added PAC export functionality
. Revised auto-suspend to ignore suspending members where contractor withholds dues

0.9.8.320

. Added NCFS ID handling

0.9.8.312

. Added update capability for Status History entries
. Fixed bug where Status window closes before reason can be entered
. Added update capability for Employment History entries
. Fixed bug in auto-suspend cutoff date computation 
. Minor bug fixes and performance improvements

0.9.8.311

. Revised computation of suspend to month end of cutoff month
. Revised reinstate fee to apply to suspend also
. Removed auto-reactivate for suspends where dues are paid

0.9.8.310

. Added auto-suspend functionality (batch)
. Fixed delinquent dues computations

0.9.8.302

. Added searchable dues paid thru date in member index
. Revised received date search on receipt index to allow range criteria	
. Minor bug fixes

0.9.8.301

. Added contractor deducts dues option
. Removed member pays option in employment
. Added receipt future date audit

0.9.8.203

. Added defaultable functionailty to address and phones
. Added Reset button RBAC rule to interface 

0.9.8.201

. Added default address and phone designation for contractors and members
. Added initiation date to Reset function in Status History
. Added autogeneration of APF when application date changes

0.9.7.101

. Added RBAC security
. Added password reset feature
. Added ability to reset dues paid thru date
. Added flash messages apparatus to main layout
. Minor bug fixes and performance improvements

0.9.6.604

. Added drop handling with auto assessment of reinstate fee (table driven)
. Added feature to activate member who pays off reinstate fee
. Revised CC handling to properly receipt CCG (not CCD) and apply previous local to CCD
. Added flash messages to member status, employment, member class and others (via submodel controller)
. Added flash message to receipt post
. UI cosmetic changes
. Minor bug fixes

0.9.6.601

. Added CC handling
. Added endable capabilities to close previous entry on insert and open previous when current entry is removed
. Added admin fee aparatus to auto price line items on receipt with standard fees ('CC', 'RN' to start)
. Minor bug fixes

0.9.6.505

. Revised remittance template export to separate by trades
. Revised receipts handling to be specific to trade
. Disabled Admin menu option (was not locked down)
. Fixed dues standing months-to-current calculations

0.9.6.504

. Replaced certain Kartik Detail View widgets not rendering properly in member and contractor view
. Fixed Expand Row display bug in member receipts summary 

0.9.6.501

. Improved force login if session is lost
. Added status handling for suspended member who pays dues
. Improved layout of receipt printing

0.9.6.101 Notes

. Added printatble receipts
. Added status handling, including updated progressive balance displays for partial payments
. Added APF-specific assessment handling, including updated progressive balance displays for partial payments
. Added member class handling
. Added member-specific receipt handling and display
. Improved some internal coding organization, cleaned out some dead code
. Fixed some bugs

0.9.5.701 Notes

. Added dues receipt move forward paid thru date functionality
. Added assessment handling, including progressive balancing of partial payments
. Fixed some bugs

0.9.5.601 Notes

. Added some receipt functionality and navigation improvements, including balancing mechanisms
. Added various drill downs for financial information by member and contractor
. Added helper dues (and hours) input for receipts
. Added last drug test date
. Fixed some bugs

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