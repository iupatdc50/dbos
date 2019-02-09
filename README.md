# dbos
### DC50 Business Office Support

Private system to manage back office operations at DC50.  

##### Main application covers:
<ul>
<li>General membership management</li>
<li>Contractors and employment management</li>
<li>Accounting</li>
<li>Special project tracking</li>
<li>Training (currently disabled)</li>
<li>Reporting</li>
</ul>

##### Modules are setup for:
<ul>
Admin, which handles settings, RBAC and other house-keeping
</ul>

##### Runtime modules handle:
<ul>
<li>Scheduled maintenance of suspended and dropped members, and JTP projects that were not awarded</li>
<li>Role-based access control hierarchy</li>
</ul>

#### **Current Issues**

_Security_
<ul>
User identity resets on occasion.
</ul>

_Navigation_
<ul>
<li>Safari browser does not properly render certain accordions (multipage and show all toggle).</li>
<li>Database caching not yet implemented.</li>
<li>Some breadcrumbs in sub-window updates are incorrect.</li>
<li>Agreements accordion closes after a panel content update.</li>
</ul>

_Special Projects_
<ul>
Deleting last registration in a project causes crash. JTP project should not have 0 registrations
</ul>
