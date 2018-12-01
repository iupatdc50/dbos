<?php

namespace app\commands;

use app\models\user\User;
use Yii;
use yii\console\Controller;
use yii\base\InvalidParamException;

class RbacController extends Controller
{

    /**
     * @return int
     * @throws \yii\base\Exception
     */
	public function actionInit()
	{
		
		if (!$this->confirm("Are you sure? It will re-create permissions tree.")) 
			return self::EXIT_CODE_NORMAL;
		
		echo "Initializing RBAC permissions and roles\n";
		$auth = Yii::$app->authManager;
		$auth->removeAll();
		
		echo "Preparing limited guest permissions";
		$uploadDocs = $auth->createPermission('uploadDocs');
		$uploadDocs->description = 'Upload scanned documents';
		$auth->add($uploadDocs);
		
		// Member permissions
		echo "Preparing member permissions";
		$browseMember = $auth->createPermission('browseMember');
		$browseMember->description = 'Browse member records';
        $auth->add($browseMember);
        $updateDemo = $auth->createPermission('updateDemo');
        $updateDemo->description = 'Update member record demographics';
        $auth->add($updateDemo);
        $browseMemberExt = $auth->createPermission('browseMemberExt');
        $browseMemberExt->description = 'Browse member records extended';
        $auth->add($browseMemberExt);
		$createMember = $auth->createPermission('createMember');
		$createMember->description = 'Create a member';
		$auth->add($createMember);
		$reportMember = $auth->createPermission('reportMember');
		$reportMember->description = 'Create a member report';
		$auth->add($reportMember);
		$resetPT = $auth->createPermission('resetPT');
		$resetPT->description = 'Reset member paid thru date';
		$auth->add($resetPT);
		$updateMember = $auth->createPermission('updateMember');
		$updateMember->description = 'Update a member';
		$auth->add($updateMember);
		$deleteMember = $auth->createPermission('deleteMember');
		$deleteMember->description = 'Delete a member';
		$auth->add($deleteMember);
		echo "...complete\n";
		// Member roles
		echo "Preparing member roles";
		$memberDocLoader = $auth->createRole('memberDocLoader');
		$memberDocLoader->description = 'Member Document Loader';
		$auth->add($memberDocLoader);
		$auth->addChild($memberDocLoader, $uploadDocs);
		$memberDemoViewer = $auth->createRole('memberDemoViewer');
		$memberDemoViewer->description = 'Member Demograph Viewer';
		$auth->add($memberDemoViewer);
		$auth->addChild($memberDemoViewer, $browseMember);
		$memberViewer = $auth->createRole('memberViewer');
		$memberViewer->description = 'Member/Employment Viewer'; 
		$auth->add($memberViewer);
		$auth->addChild($memberViewer, $memberDemoViewer);
		$auth->addChild($memberViewer, $browseMemberExt);
		$memberDemoEditor = $auth->createRole('memberDemoUpdater');
		$memberDemoEditor->description = 'Member Demograph Editor';
		$auth->add($memberDemoEditor);
		$auth->addChild($memberDemoEditor, $memberDemoViewer);
		$auth->addChild($memberDemoEditor, $updateDemo);
		$memberEditor = $auth->createRole('memberEditor');
		$memberEditor->description = 'Member/Employment Editor'; 
		$auth->add($memberEditor);
		$auth->addChild($memberEditor, $memberDocLoader);
		$auth->addChild($memberEditor, $memberViewer);
		$auth->addChild($memberEditor, $createMember);
		$auth->addChild($memberEditor, $reportMember);
        $auth->addChild($memberEditor, $updateDemo);
		$auth->addChild($memberEditor, $updateMember);
		$memberAdmin = $auth->createRole('memberAdmin');
		$memberAdmin->description = 'Member/Employment Admin'; 
		$auth->add($memberAdmin);
		$auth->addChild($memberAdmin, $memberEditor);
		$auth->addChild($memberAdmin, $deleteMember);
		$auth->addChild($memberAdmin, $resetPT);
		echo "...complete\n";

		// Contractor permissions
		echo "Preparing contractor permissions";
		$browseContractor = $auth->createPermission('browseContractor');
		$browseContractor->description = 'Browse contractor records';
		$auth->add($browseContractor);
		$createContractor = $auth->createPermission('createContractor');
		$createContractor->description = 'Create a contractor';
		$auth->add($createContractor);
		$reportContractor = $auth->createPermission('reportContractor');
		$reportContractor->description = 'Create a contractor report';
		$auth->add($reportContractor);
		$updateContractor = $auth->createPermission('updateContractor');
		$updateContractor->description = 'Update a contractor';
		$auth->add($updateContractor);
		$deleteContractor = $auth->createPermission('deleteContractor');
		$deleteContractor->description = 'Delete a contractor';
		$auth->add($deleteContractor);
		echo "...complete\n";
		// Contractor roles
		echo "Preparing contractor roles";
		$contractorViewer = $auth->createRole('contractorViewer');
		$contractorViewer->description = 'Contractor Viewer';
		$auth->add($contractorViewer);
		$auth->addChild($contractorViewer, $browseContractor);
		$contractorEditor = $auth->createRole('contractorEditor');
		$contractorEditor->description = 'Contractor Editor';
		$auth->add($contractorEditor);
		$auth->addChild($contractorEditor, $contractorViewer);
		$auth->addChild($contractorEditor, $createContractor);
		$auth->addChild($contractorEditor, $reportContractor);
		$auth->addChild($contractorEditor, $updateContractor);
		$contractorAdmin = $auth->createRole('contractorAdmin');
		$contractorAdmin->description = 'Contractor Admin';
		$auth->add($contractorAdmin);
		$auth->addChild($contractorAdmin, $contractorEditor);
		$auth->addChild($contractorAdmin, $deleteContractor);
		echo "...complete\n";
		
		// Accounting permissions
		echo "Preparing accounting permissions";
		$browseReceipt = $auth->createPermission('browseReceipt');
		$browseReceipt->description = 'Browse receipts';
		$auth->add($browseReceipt);
		$createReceipt = $auth->createPermission('createReceipt');
		$createReceipt->description = 'Create a receipt';
		$auth->add($createReceipt);
		$createInvoice = $auth->createPermission('createInvoice');
		$createInvoice->description = 'Create a contractor invoice';
		$auth->add($createInvoice);
		$reportAccounting = $auth->createPermission('reportAccounting');
		$reportAccounting->description = 'Create an accounting report';
		$auth->add($reportAccounting);
		$updateReceipt = $auth->createPermission('updateReceipt');
		$updateReceipt->description = 'Update a receipt';
		$auth->add($updateReceipt);
		$deleteReceipt = $auth->createPermission('deleteReceipt');
		$deleteReceipt->description = 'Delete a receipt';
		$auth->add($deleteReceipt);
		echo "...complete\n";
		// Accounting roles
		echo "Preparing accounting roles";
		$accountingViewer = $auth->createRole('accountingViewer');
		$accountingViewer->description = 'Accounting Viewer';
		$auth->add($accountingViewer);
		$auth->addChild($accountingViewer, $browseReceipt);
		$accountingReviewer = $auth->createRole('accountingReviewer');
		$accountingReviewer->description = 'Accounting Reviewer';
		$auth->add($accountingReviewer);
		$auth->addChild($accountingReviewer, $accountingViewer);
		$auth->addChild($accountingReviewer, $reportAccounting);
		$accountingEditor = $auth->createRole('accountingEditor');
		$accountingEditor->description = 'Accounting Editor';
		$auth->add($accountingEditor);
		$auth->addChild($accountingEditor, $accountingReviewer);
		$auth->addChild($accountingEditor, $createReceipt);
		$auth->addChild($accountingEditor, $createInvoice);
		$auth->addChild($accountingEditor, $updateReceipt);
		$accountingAdmin = $auth->createRole('accountingAdmin');
		$accountingAdmin->description = 'Accounting Admin';
		$auth->add($accountingAdmin);
		$auth->addChild($accountingAdmin, $accountingEditor);
		$auth->addChild($accountingAdmin, $deleteReceipt);
		echo "...complete\n";
		
		// Project permissions
		echo "Preparing project permissions";
		$browseProject = $auth->createPermission('browseProject');
		$browseProject->description = 'Browse projects';
		$auth->add($browseProject);
		$manageProject = $auth->createPermission('manageProject');
		$manageProject->description = 'Create or update a project';
		$auth->add($manageProject);
		$deleteProject = $auth->createPermission('deleteProject');
		$deleteProject->description = 'Delete a project';
		$auth->add($deleteProject);
		echo "...complete\n";
		// Project roles
		echo "Preparing project roles";
		$projectViewer = $auth->createRole('projectViewer');
		$projectViewer->description = 'Project Viewer';
		$auth->add($projectViewer);
		$auth->addChild($projectViewer, $browseProject);
		$projectEditor = $auth->createRole('projectEditor');
		$projectEditor->description = 'Project Editor';
		$auth->add($projectEditor);
		$auth->addChild($projectEditor, $projectViewer);
		$auth->addChild($projectEditor, $manageProject);
		$projectAdmin = $auth->createRole('projectAdmin');
		$projectAdmin->description = 'Project Admin';
		$auth->add($projectAdmin);
		$auth->addChild($projectAdmin, $projectEditor);
		$auth->addChild($projectAdmin, $deleteProject);
		echo "...complete\n";
		
		// Training permissions
		echo "Preparing training permissions";
		$browseTraining = $auth->createPermission('browseTraining');
		$browseTraining->description = 'Browse training content';
		$auth->add($browseTraining);
		$manageTraining = $auth->createPermission('manageTraining');
		$manageTraining->description = 'Manage training information';
		$auth->add($manageTraining);
		echo "...complete\n";
		// Training roles
		echo "Preparing training roles";
		$trainingViewer = $auth->createRole('trainingViewer');
		$trainingViewer->description = 'Training Viewer';
		$auth->add($trainingViewer);
		$auth->addChild($trainingViewer, $browseTraining);
		$trainingEditor = $auth->createRole('trainingEditor');
		$trainingEditor->description = 'Training Editor';
		$auth->add($trainingEditor);
		$auth->addChild($trainingEditor, $trainingViewer);
		$auth->addChild($trainingEditor, $manageTraining);
		echo "...complete\n";
		
		// Support permissions
		echo "Preparing support permissions";
		$manageSupport = $auth->createPermission('manageSupport');
		$manageSupport->description = 'Manage support information';
		$auth->add($manageSupport);
		echo "...complete\n";
		
		// Support roles
		echo "Preparing support roles";
		$supportEditor = $auth->createRole('supportEditor');
		$supportEditor->description = 'Support Editor';
		$auth->add($supportEditor);
		$auth->addChild($supportEditor, $manageSupport);
		echo "...complete\n";
		
		// Special permissions
		echo "Preparing special permissions";
		$showReportMenu = $auth->createPermission('showReportMenu');
		$showReportMenu->description = 'Show Report Menu';
		$auth->add($showReportMenu);
		$auth->addChild($reportAccounting, $showReportMenu);
		$auth->addChild($reportContractor, $showReportMenu);
		$auth->addChild($reportMember, $showReportMenu);
		echo "...complete\n";
		
		// Limited roles
		echo "Preparing limited access roles";
		$uploadUser = $auth->createRole('uploadUser');
		$uploadUser->description = 'Document uploader';
		$auth->add($uploadUser);
		$auth->addChild($uploadUser, $memberDocLoader);
		echo "...complete\n";
		
		// User account permissions
		echo "Preparing user account permissions";
		$browseUser = $auth->createPermission('browseUser');
		$browseUser->description = 'Browse user records';
		$auth->add($browseUser);
		$updateUser = $auth->createPermission('updateUser');
		$updateUser->description = 'Update user account information';
		$auth->add($updateUser);
		$ownerRule = new \app\rbac\OwnerRule;
		$auth->add($ownerRule);
		$updateOwnProfile = $auth->createPermission('updateOwnProfile');
		$updateOwnProfile->description = 'Update own profile information';
		$updateOwnProfile->ruleName = $ownerRule->name;
		$auth->add($updateOwnProfile);
		$auth->addChild($updateOwnProfile, $updateUser);
		$assignRole = $auth->createPermission('assignRole');
		$assignRole->description = 'Assign role to user';
		$auth->add($assignRole);
		$deleteUser = $auth->createPermission('deleteUser');
		$deleteUser->description = 'Delete user account';
		$auth->add($deleteUser);
		echo "...complete\n";
		
		// User account roles
		echo "Preparing user account roles";
		$accountOwner = $auth->createRole('accountOwner');
		$accountOwner->description = 'User account owner';
		$auth->add($accountOwner);
		$auth->addChild($accountOwner, $updateOwnProfile);
		$accountAdmin = $auth->createRole('accountAdmin');
		$accountAdmin->description = 'User account admin';
		$auth->add($accountAdmin);
		$auth->addChild($accountAdmin, $updateUser);
		$auth->addChild($accountAdmin, $assignRole);
		$auth->addChild($accountAdmin, $deleteUser);
		echo "...complete\n";
		
		// Front Desk role
		echo "Preparing Front Desk role";
		$frontDesk = $auth->createRole('frontDesk');
		$frontDesk->description = 'Front Desk Staff';
		$auth->add($frontDesk);
		$auth->addChild($frontDesk, $memberEditor);
		$auth->addChild($frontDesk, $contractorEditor);
		$auth->addChild($frontDesk, $accountingEditor);
		echo "...complete\n";
		
		// Office Manager role
		echo "Preparing Office Manager role";
		$officeMgr = $auth->createRole('officeMgr');
		$officeMgr->description = 'Office Manager';
		$auth->add($officeMgr);
		$auth->addChild($officeMgr, $assignRole);
		$auth->addChild($officeMgr, $memberAdmin);
		$auth->addChild($officeMgr, $contractorAdmin);
		$auth->addChild($officeMgr, $accountingAdmin);
		$auth->addChild($officeMgr, $projectAdmin);
		echo "...complete\n";
		
		//Business Rep role
		echo "Preparing Business Rep role";
		$bizMgr = $auth->createRole('bizRep');
		$bizMgr->description = 'Business Rep';
		$auth->add($bizMgr);
		$auth->addChild($bizMgr, $memberViewer); 
		$auth->addChild($bizMgr, $contractorViewer);
		echo "...complete\n";
		
		//Training role
		echo "Preparing Training role";
		$training = $auth->createRole('training');
		$training->description = 'Training Staff';
		$auth->add($training);
		$auth->addChild($training, $memberDemoEditor);
		$auth->addChild($training, $contractorViewer);
		$auth->addChild($training, $trainingViewer);
		echo "...complete\n";
		
		// System Admin role
		echo "Preparing System Admin role";
		$sysAdmin = $auth->createRole('sysAdmin');
		$sysAdmin->description = 'System Administrator';
		$auth->add($sysAdmin);
		$auth->addChild($sysAdmin, $memberAdmin);
		$auth->addChild($sysAdmin, $contractorAdmin);
		$auth->addChild($sysAdmin, $accountingAdmin);
		$auth->addChild($sysAdmin, $projectAdmin);
		$auth->addChild($sysAdmin, $trainingEditor);
		$auth->addChild($sysAdmin, $accountAdmin);
		echo "...complete\n";
		
		echo "Done! Permission tree created\n";
		
	}
	
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
		$access = $auth->checkAccess($user->id, $rule) ? 'has' : 'does not have';
		echo "{$username} {$access} `{$rule}` permission.\n";
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
	
