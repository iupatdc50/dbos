<?php /** @noinspection PhpUnused */

namespace app\commands;

use app\models\user\User;
use app\rbac\OwnerRule;
use Exception;
use Yii;
use yii\base\Exception as BaseException;
use yii\console\Controller;
use yii\base\InvalidParamException;
use yii\rbac\Item;
use yii\rbac\ManagerInterface;
use yii\rbac\Permission;
use yii\rbac\Role;
use yii\rbac\Rule;

class RbacController extends Controller
{

    /* @var $_auth ManagerInterface */
    private $_auth;

    public function init()
    {
        $this->_auth = Yii::$app->authManager;
    }

    /**
     * Clears all permissions and roles and rebuilds the entire tree.
     *
     * @return int
     * @throws BaseException
     * @throws Exception
     */
	public function actionInit()
	{
		
		if (!$this->confirm("Are you sure? It will re-create permissions tree.")) 
			return self::EXIT_CODE_NORMAL;
		
		echo "\n=====> Initializing RBAC permissions and functional roles\n\n";
		$this->_auth->removeAll();
		
		echo "Preparing limited guest permissions";
		$uploadDocs = $this->addPermission('uploadDocs', 'Upload scanned docs');
        echo "...complete\n";

		// Member permissions
		echo "Preparing member permissions";
        $browseMember = $this->addPermission('browseMember', 'Browse member records');
        $updateDemo = $this->addPermission('updateDemo', 'Update member record demographics');
        $browseMemberExt = $this->addPermission('browseMemberExt', 'Browse member records extended');
        $createMember = $this->addPermission('createMember', 'Create a member');
        $reportMember = $this->addPermission('reportMember', 'Create a member report');
        $resetPT = $this->addPermission('resetPT', 'Reset member paid thru date');
        $updateMember = $this->addPermission('updateMember', 'Update a member');
        $deleteMember = $this->addPermission('deleteMember', 'Delete a member');
		echo "...complete\n";

		// Member roles
		echo "Preparing member roles";
        $memberAdmin = $this->addRole('memberAdmin', 'Member/Employment Admin');
        $memberEditor = $this->addRole('memberEditor', 'Member/Employment Editor', [$memberAdmin]);
        $memberDemoEditor = $this->addRole('memberDemoEditor', 'Member Demograph Editor');
        $memberViewer = $this->addRole('memberViewer', 'Member/Employment Viewer', [$memberEditor]);
        $memberDemoViewer = $this->addRole('memberDemoViewer', 'Member Demograph Viewer', [$memberViewer, $memberDemoEditor]);
		$memberDocLoader = $this->addRole('memberDocLoader', 'Member Document Loader', [$memberEditor]);
		$this->adopt($memberDocLoader, [$uploadDocs]);
		$this->adopt($memberDemoViewer, [$browseMember]);
		$this->adopt($memberViewer, [$browseMemberExt]);
		$this->adopt($memberDemoEditor, [$updateDemo]);
		$this->adopt($memberEditor, [$createMember, $reportMember, $updateDemo, $updateMember]);
		$this->adopt($memberAdmin, [$deleteMember, $resetPT]);
		echo "...complete\n";

		// Contractor permissions
		echo "Preparing contractor permissions";
        $browseContractor = $this->addPermission('browseContractor', 'Browse contractor records');
        $browseCJournal = $this->addPermission('browseCJournal', 'Browse contractor journal notes');
        $manageCJournal = $this->addPermission('manageCJournal', 'Create or delete contractor journal notes');
        $createContractor = $this->addPermission('createContractor', 'Create a contractor');
        $reportContractor = $this->addPermission('reportContractor', 'Create a contractor report');
        $updateContractor = $this->addPermission('updateContractor', 'Update a contractor');
        $deleteContractor = $this->addPermission('deleteContractor', 'Delete a contractor');
		echo "...complete\n";

		// Contractor roles
		echo "Preparing contractor roles";
        $contractorAdmin = $this->addRole('contractorAdmin', 'Contractor Admin');
        $contractorEditor = $this->addRole('contractorEditor', 'Contractor Editor', [$contractorAdmin]);
        $contractorJournaler = $this->addRole('contractorJournaler', 'Contractor Journaler', [$contractorEditor]);
        $contractorJournalViewer = $this->addRole('contractorJournalViewer', 'Contractor Journal Viewer', [$contractorJournaler]);
        $contractorViewer = $this->addRole('contractorViewer', 'Contractor Viewer', [$contractorJournalViewer]);
        $this->adopt($contractorViewer, [$browseContractor]);
        $this->adopt($contractorJournalViewer, [$browseCJournal]);
        $this->adopt($contractorJournaler, [$manageCJournal]);
        $this->adopt($contractorEditor, [$createContractor, $reportContractor, $updateContractor]);
        $this->adopt($contractorAdmin, [$deleteContractor]);
		echo "...complete\n";
		
		// Accounting permissions
		echo "Preparing accounting permissions";
        $browseReceipt = $this->addPermission('browseReceipt', 'Browse receipts');
        $createReceipt = $this->addPermission('createReceipt', 'Create a receipt');
        $createInvoice = $this->addPermission('createInvoice', 'Create a contractor invoice');
        $reportAccounting = $this->addPermission('reportAccounting', 'Create an accounting report');
        $updateReceipt = $this->addPermission('updateReceipt', 'Update a receipt');
        $deleteReceipt = $this->addPermission('deleteReceipt', 'Delete a receipt');
		echo "...complete\n";

 		// Accounting roles
		echo "Preparing accounting roles";
        $accountingAdmin = $this->addRole('accountingAdmin', 'Accounting Admin');
        $accountingEditor = $this->addRole('accountingEditor', 'Accounting Editor', [$accountingAdmin]);
        $accountingReviewer = $this->addRole('accountingReviewer', 'Accounting Reviewer', [$accountingEditor]);
        $accountingViewer = $this->addRole('accountingViewer', 'Accounting Viewer', [$accountingReviewer]);
        $this->adopt($accountingViewer, [$browseReceipt]);
        $this->adopt($accountingReviewer, [$reportAccounting]);
        $this->adopt($accountingEditor, [$createReceipt, $createInvoice, $updateReceipt]);
        $this->adopt($accountingAdmin, [$deleteReceipt]);
		echo "...complete\n";
		
		// Project permissions
		echo "Preparing project permissions";
        $browseProject = $this->addPermission('browseProject', 'Browse special projects');
        $manageProject = $this->addPermission('manageProject', 'Create or update a special project');
        $deleteProject = $this->addPermission('deleteProject', 'Delete a special project');
		echo "...complete\n";

		// Project roles
		echo "Preparing project roles";
        $projectAdmin = $this->addRole('projectAdmin', 'Project Admin');
        $projectEditor = $this->addRole('projectEditor', 'Project Editor', [$projectAdmin]);
		$projectViewer = $this->addRole('projectViewer', 'Project Viewer', [$projectEditor]);
        $this->adopt($projectViewer, [$browseProject]);
        $this->adopt($projectEditor, [$manageProject]);
        $this->adopt($projectAdmin, [$deleteProject]);
		echo "...complete\n";
		
		// Training permissions
		echo "Preparing training permissions";
		$browseTraining = $this->addPermission('browseTraining', 'Browse training content');
		$manageTraining = $this->addPermission('manageTraining', 'Create or update training information');
		echo "...complete\n";

		// Training roles
		echo "Preparing training roles";
		$trainingEditor = $this->addRole('trainingEditor', 'Training Editor');
		$trainingViewer = $this->addRole('trainingViewer', 'Training Viewer', [$trainingEditor]);
		$this->adopt($trainingViewer, [$browseTraining]);
		$this->adopt($trainingEditor, [$manageTraining]);
		echo "...complete\n";
		
		// Support permissions
		echo "Preparing support permissions";
		$manageSupport = $this->addPermission('manageSupport', 'Manage support information');
		echo "...complete\n";
		
		// Support roles
		echo "Preparing support roles";
		$supportEditor = $this->addRole('supportEditor', 'Support Editor');
		$this->adopt($supportEditor, [$manageSupport]);
		echo "...complete\n";
		
		// Special permissions
		echo "Preparing special permissions";
		$this->addPermission('showReportMenu', 'Show Report Menu', [
            $reportAccounting,
            $reportContractor,
            $reportMember,
        ]);
		echo "...complete\n";
		
		// Limited roles
		echo "Preparing limited access roles";
		$uploadUser = $this->addRole('uploadUser', 'Document uploader');
		$this->adopt($uploadUser, [$memberDocLoader]);
		echo "...complete\n";
		
		// User account permissions
		echo "Preparing user account permissions";
		$browseUser = $this->addPermission('browseUser', 'Browse user records');
		$updateUser = $this->addPermission('updateUser', 'Update user account information');
		$ownerRule = new OwnerRule;
		$this->_auth->add($ownerRule);
		$updateOwnProfile = $this->addPermission('updateOwnProfile', 'Update own profile information', [], $ownerRule);
		$this->adopt($updateOwnProfile, [$updateUser]);
		$assignRole = $this->addPermission('assignRole', 'Assign role to user');
		$deleteUser = $this->addPermission('deleteUser', 'Delete user account');
		echo "...complete\n";
		
		// User account roles
		echo "Preparing user account roles";
		$accountOwner = $this->addRole('accountOwner', 'User account owner');
		$this->adopt($accountOwner, [$updateOwnProfile]);
		$accountAdmin = $this->addRole('accountAdmin', 'User account admin');
		$this->adopt($accountAdmin, [$browseUser, $updateUser, $assignRole, $deleteUser]);
		echo "...complete\n";

		echo "\n=====> Initializing and preparing staff roles\n\n";

		// Front Desk role
		echo "Preparing Front Desk role";
		$frontDesk = $this->addRole('frontDesk', 'Front Desk Staff*');
		$this->adopt($frontDesk, [$memberEditor, $contractorEditor, $accountingEditor, $trainingViewer]);
		echo "...complete\n";
		
		// Office Manager role
		echo "Preparing Office Manager role";
		$officeMgr = $this->addRole('officeMgr', 'Office Manager*');
		$this->adopt($officeMgr, [$accountAdmin, $memberAdmin, $contractorAdmin, $accountingAdmin, $trainingEditor, $projectAdmin]);
		echo "...complete\n";
		
		//Business Rep role
		echo "Preparing Business Rep role";
		$bizMgr = $this->addRole('bizRep', 'Business Rep*');
		$this->adopt($bizMgr, [$memberViewer, $contractorJournalViewer, $accountingViewer]);
		echo "...complete\n";
		
		//Training role
		echo "Preparing Training role";
		$training = $this->addRole('training', 'Training Staff*');
		$this->adopt($training, [$memberDemoEditor, $contractorViewer, $trainingEditor]);
		echo "...complete\n";
		
		// System Admin role
		echo "Preparing System Admin role";
		$sysAdmin = $this->addRole('sysAdmin', 'System Administrator*');
		$this->adopt($sysAdmin, [$memberAdmin, $contractorAdmin, $accountingAdmin, $projectAdmin, $trainingEditor, $accountAdmin]);
		echo "...complete\n";
		
		echo "\n=====> Done! Permission tree created\n\n";

        echo "****************************************************************\n";
        echo "***                                                          ***\n";
        echo "*** NOTE:  Remember to assign users to staff roles           ***\n";
        echo "***        Command file ./rbac_assign                        ***\n";
        echo "***                                                          ***\n";
        echo "****************************************************************\n\n";

		return null;
		
	}

    /**
     * Assigns a role to a user
     *
     * @param $role
     * @param $username
     * @throws Exception
     */
	public function actionAssign($role, $username)
	{
		$user = $this->findUser($username);
	
		$auth = Yii::$app->authManager;
		$roleObject = $auth->getRole($role);
		if (!$roleObject) {
			throw new InvalidParamException("There is no role \"$role\".");
		}
	
		$auth->assign($roleObject, $user->id);
		echo "The role {$role} was assigned to user {$username} \n";
	
	}

    /**
     * Revokes a user's role
     *
     * @param $role
     * @param $username
     */
	public function actionRevoke($role, $username)
	{
		$user = $this->findUser($username);
	
		$auth = Yii::$app->authManager;
		$roleObject = $auth->getRole($role);
		if (!$roleObject) {
			throw new InvalidParamException("There is no role \"$role\".");
		}
	
		$auth->revoke($roleObject, $user->id);
		echo "The role {$role} was revoked from user {$username} \n";
	
	}

    /**
     * Revokes all permissions assigned to a user
     *
     * @param $username
     */
	public function actionRevokeAll($username)
	{
		$user = $this->findUser($username);
	
		$auth = Yii::$app->authManager;	
		$auth->revokeAll($user->id);
		echo "All roles for user {$username} were revoked \n";
	
	}
	
	public function actionTest($username, $rule, $params = [])
	{
		$user = $this->findUser($username);
		$auth = Yii::$app->authManager;	
		$access = $auth->checkAccess($user->id, $rule, $params) ? 'has' : 'does not have';
		echo "{$username} {$access} `{$rule}` permission.\n";
	}

    /**
     * @param $name
     * @param $descrip
     * @param array $parents
     * @param Rule|null $rule
     * @return Permission
     * @throws Exception
     */
    private function addPermission ($name, $descrip, $parents = [], Rule $rule = null)
    {
        $permission = $this->_auth->createPermission($name);
        $permission->description = $descrip;
        if (isset($rule))
            $permission->ruleName = $rule->name;
        $this->_auth->add($permission);

        foreach ($parents as $parent)
            $this->adopt($parent, [$permission]);

        return $permission;
    }

    /**
     * @param $name
     * @param $descrip
     * @param array $parents
     * @return Role
     * @throws Exception
     */
    private function addRole($name, $descrip, $parents = [])
    {
        $role = $this->_auth->createRole($name);
        $role->description = $descrip;
        $this->_auth->add($role);

        foreach ($parents as $parent)
            $this->adopt($parent, [$role]);

        return $role;
    }

    /**
     * @param $parent
     * @param array $children
     * @throws BaseException
     */
	private function adopt($parent, $children = [])
    {
        $role = ($parent instanceof Item) ? $parent : $this->_auth->getRole($parent);
        if (!$role)
            throw new InvalidParamException("There is no role `{$parent}`.");
        foreach ($children as $child) {
            if (!($child instanceof Item))
                throw new InvalidParamException("Passed child is not an Item object.");
            $this->_auth->addChild($role, $child);
        }
    }
	
	private function findUser($username)
	{
		$user = User::find()->where(['username' => $username])->one();
		if (!$user) {
			throw new InvalidParamException("There is no user \"$username\".");
		}
		return $user;	
	}
			
}
	
