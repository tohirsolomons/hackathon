<?php

class DBModel_Programs extends \Tohir\DBModel
{

    /**
     * @var string Name of Table
     */
    protected $tableName = 'programschedule';

    /**
     * @var string Primary of Table
     */
    protected $primaryKey = 'id';

    /**
     * @var string The column holding the Date Inserted Field - auto populated if set
     */
    protected $dateInsertColumn = 'date_created';

    /**
     * @var string The column holding the Date Updated Field - auto populated if set
     */
    protected $dateUpdateColumn = 'date_updated';

    protected $tableColumns = [
            'programid',
            'channel_tag',
            'title',
            'program_date',
            'starttime',
            'endtime',
        ];

    public function hasProgramme($progId, $date, $time, $channel)
    {
        $result = $this->db->select($this->tableName, ['programid'=>$progId, 'program_date'=>$date, 'starttime'=>$time, 'channel_tag'=>$channel]);

        if (count($result) == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function getSchedule($channelTag, $date)
    {
        $sql = <<<sql
SELECT programschedule.*, programinfo.description, season_id, episode_id, showinfo.shorturl, showtype, showinfo.showimage
FROM programschedule
INNER JOIN programinfo ON (programschedule.programid = programinfo.programid)
LEFT JOIN showinfo ON (programschedule.title = showinfo.internaltitle)
WHERE channel_tag = :channel_tag AND program_date=:program_date
ORDER BY starttime ASC
sql;

        return $this->db->query($sql, ['channel_tag'=>$channelTag, 'program_date'=>$date]);
    }

    public function getForEndDateFix()
    {
        return $this->db->select($this->tableName, null, null, null, ['channel_tag'=>'asc', 'program_date'=>'desc', 'starttime'=>'desc']);
    }


    public function getDistinctTitles($channelList)
    {
        $list = explode('|', $channelList);

        $string = '';
        $join = '';

        foreach ($list as $item)
        {
            $string .= $join."'{$item}'";
            $join = ', ';
        }

        return $this->db->query('select programschedule.title, programschedule.programid, programinfo.season_id from programschedule 
        INNER JOIN programinfo ON (programschedule.programid = programinfo.programid)
        WHERE channel_tag IN ('.$string.')  group by title');
    }

    public function getScheduleForShow($show)
    {
        $sql = <<<sql
SELECT programschedule.*, programinfo.description, season_id, episode_id, showinfo.shorturl, showtype, showinfo.showimage, channelname, channelnumber, channeltag, channellogo
FROM programschedule
INNER JOIN programinfo ON (programschedule.programid = programinfo.programid)
INNER JOIN channels ON (programschedule.channel_tag = channels.channeltag)
LEFT JOIN showinfo ON (programschedule.title = showinfo.internaltitle)
WHERE programschedule.title = :show
ORDER BY program_date, starttime ASC
sql;

        return $this->db->query($sql, ['show'=>$show], null, null, ['program_date'=>'asc', 'startime'=>'asc']);
    }


}