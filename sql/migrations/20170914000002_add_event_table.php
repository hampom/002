<?php


use Phinx\Migration\AbstractMigration;

class AddEventTable extends AbstractMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         $table = $this->table('event');
         $table->addColumn('calendar_id', 'integer', ['signed' => false ])
               ->addColumn('title', 'string')
               ->addColumn('description', 'text')
               ->addColumn('startAt', 'datetime')
               ->addColumn('endAt', 'datetime')
               ->save();
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         $this->dropTable('event');
     }
}
