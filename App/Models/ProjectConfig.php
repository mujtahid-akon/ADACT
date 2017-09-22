<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/20/17
 * Time: 5:35 PM
 */

namespace AWorDS\App\Models;

/**
 * Class ProjectConfig.
 *
 * variables extracted from $this->config
 *
 * @property string $project_name  Name of the project
 * @property string $aw_type       Absent Word Type (maw|raw)
 * @property string $sequence_type Minimal Absent Word Type (nucleotide|protein)
 * @property array  $kmer          K-Mer [max, min]
 * @property bool   $inversion     Use Inversion ?
 * @property string $dissimilarity_index Dissimilarity Index for MAW or RAW
 * @property array  $data          Containing all the InputAnalyzer.results data
 * @property string $type          FASTA file getting method (file|accn_gin)
 * @property string $file_id       md5 sum of uploaded file directory
 */
class ProjectConfig extends Model{
    private $_config_file;
    /**
     * @var array $_config_data
     */
    private $_config_data;

    /**
     * @var array
     */
    public $dissimilarity_indexes = [
        "MAW" => [
            "MAW_LWI_SDIFF" => "Length weighted index of symmetric difference of MAW sets",
            "MAW_LWI_INTERSECT" => "Length weighted index of intersection of MAW sets",
            "MAW_GCC_SDIFF" => "GC content of symmetric difference of MAW sets",
            "MAW_GCC_INTERSECT" => "GC content of intersection of MAW sets",
            "MAW_JD" => "Jaccard Distance of MAW sets",
            "MAW_TVD" => "Total Variation Distance of MAW sets"
        ],
        "RAW" => [
            "RAW_LWI" => "Length weighted index of RAW set",
            "RAW_GCC" => "GC content of RAW set"
        ]
    ];

    function __construct($config_file = null){
        parent::__construct();
        $this->_config_file = $config_file;
        if(file_exists($config_file)) $this->_load_config(true);
    }

    /**
     * load_config method.
     *
     * Load configuration file as an array instead of file
     *
     * @param array $config_data
     */
    function load_config($config_data){
        $this->_config_data = $config_data;
        $this->_load_config(false);
    }

    function getConfigAssocArray(){
        return $this->_config_data;
    }

    function getConfigJSON(){
        return json_encode($this->_config_data, JSON_PRETTY_PRINT);
    }

    /**
     * verify method
     *
     * Check whether the provided config is valid or not
     * FIXME
     *
     * @return bool
     */
    public function verify(){
        extract($this->_config_data);
        /**
         * variables extracted from $this->config
         *
         * @var string $project_name  Name of the project
         * @var string $aw_type       Absent Word Type (maw|raw)
         * @var string $sequence_type Minimal Absent Word Type (nucleotide|protein)
         * @var array  $kmer          K-Mer [max, min]
         * @var bool   $inversion     Use Inversion ?
         * @var string $dissimilarity_index Dissimilarity Index for MAW or RAW
         * @var array  $data          Containing all the InputAnalyzer.results data
         * @var string $type          FASTA file getting method (file|accn_gin)
         * @var string $file_id       sha512 of uploaded file directory
         */

        $d_i_maw = array_keys($this->dissimilarity_indexes['MAW']); // ['MAW_LWI_SDIFF', 'MAW_LWI_INTERSECT', 'MAW_GCC_SDIFF', 'MAW_GCC_INTERSECT', 'MAW_JD', 'MAW_TVD'];
        $d_i_raw = array_keys($this->dissimilarity_indexes['RAW']); // ['RAW_LWI', 'RAW_GCC'];

        if(isset($project_name) AND $project_name != null
            AND isset($aw_type) AND in_array($aw_type, ['maw', 'raw'])
            AND isset($kmer, $kmer['min'], $kmer['max'])
            AND isset($inversion)
            AND ($aw_type == 'maw' OR $aw_type == 'raw')
            AND isset($sequence_type) AND in_array($sequence_type, ['nucleotide', 'protein'])
            AND isset($dissimilarity_index)
            AND (($aw_type == 'maw'   AND in_array($dissimilarity_index, $d_i_maw))
                OR ($aw_type == 'raw' AND in_array($dissimilarity_index, $d_i_raw)))
            AND isset($type) AND in_array($type, ['file', 'accn_gin'])
            AND (($type == 'file' AND (new FileUploader())->getFromID($file_id) !== false) OR ($type == 'accn_gin'))
            AND isset($data)
        ) return true;
        return false;
    }

    private function _load_config($is_a_file){
        if($is_a_file) $this->_config_data = json_decode(file_get_contents($this->_config_file), true);

        foreach ($this->_config_data as $title => $value){
            $this->$title = $value;
        }
    }
}