<?php

namespace app\models\base;

/**
 * Provides signatures for models that have associated note models
 *
 */
interface iNotableInterface {
	
	/**
	 * Adds a journal note to this project
	 *
	 * @param Model $note Note model that will be saved
	 */
	public function addNote($note);
	
	/**
	 * @return \yii\db\ActiveQuery Note[] array
	 */
	public function getNotes();
	
	/**
	 * @return integer Count of notes for this model
	 */
	public function getNoteCount();
	
}