//
// The Match7.java code has be copied from http://www.itu.dk/~sestoft/bsa/Match7.java.
// Then some modifications were made to it for customizing the rendering and printing
// of the phylogenetic tree. The algorithms to construct the tree have not been
// modified in any ways. The original file comment follows below:
//
//
// Implementation of some algorithms for building phylogenetic trees from
// Durbin et al: Biological Sequence Analysis, CUP 1998, chapter 7.
// Peter Sestoft, sestoft@itu.dk 1999-12-07 version 0.3
// Reference:  http://www.itu.dk/people/sestoft/bsa.html

// License: Anybody can use this code for any purpose, including
// teaching, research, and commercial purposes, provided proper
// reference is made to its origin.  Neither the author nor the Royal
// Veterinary and Agricultural University, Copenhagen, Denmark, can
// take any responsibility for the consequences of using this code.
// Compile with:
//      javac Match7.java
// Run with:
//      java Match7
import java.awt.*;
import java.awt.event.*;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import javax.imageio.ImageIO;

// The abstract class of clusters or rooted trees
abstract class Cluster {

    public abstract void draw(Graphics g, int w, int h);
}

// UPGMA clusters or trees, built by the UPGMA algorithm
class UPCluster extends Cluster {

    String label;               // Cluster identifier
    int card;			// The number of sequences in the cluster
    UPCluster left, right;	// Left and right children, or null
    double[] dmat;		// Distances to lower-numbered nodes, or null

    public UPCluster(String label, double[] dmat) {	// Leaves = single sequences
        this.label = label;
        card = 1;
        this.dmat = dmat;
    }

    public UPCluster(String label, UPCluster left, UPCluster right, double[] dmat) {
        this.label = label;
        this.left = left;
        this.right = right;
        card = left.card + right.card;
        this.dmat = dmat;
    }

    public boolean live() {
        return dmat != null;
    }

    public void kill() {
        dmat = null;
    }

    public void draw(Graphics g, int w, int h) {
        draw(g, w, h, 0, (double) w / card, 10);
    }

  // Draw tree and return x-coordinate of root
    public int draw(Graphics g, int w, int h, int leftcard,
            double xsc, int fromy) {
        if (left != null && right != null) {	// Internal node
            int y = fromy + 20;
            int leftx = left.draw(g, w, h, leftcard, xsc, y);
            int rightx = right.draw(g, w, h, leftcard + left.card, xsc, y);
            g.drawLine(leftx, y, rightx, y);
            int x = (leftx + rightx) / 2;
            g.drawLine(x, y, x, fromy);
            return x;
        } else {					// Leaf node
            int x = (int) ((leftcard + 0.5) * xsc);

            g.drawLine(x, fromy + 20, x, fromy);
            //g.fillOval(x - 4, fromy + 20 - 4, 8, 8);
            //g.drawString(label, x - 5, fromy + 20 + 15);
            g.drawString(label, x - 3*label.length(), fromy + 20 + 15);
            
            return x;
        }
    }
}

// The UPGMA algorithm
class UPGMA {

    int K;			// The number of clusters created so far
    UPCluster[] cluster;		// The nodes (clusters) of the resulting tree

    public UPGMA(String[] rgSpecies, double[][] ds) {
        int N = ds.length;
        cluster = new UPCluster[2 * N - 1];
        
        assert(rgSpecies.length == N);
                
        for (int i = 0; i < N; i++) {
            cluster[i] = new UPCluster(rgSpecies[i], ds[i]);
        }
        K = N;
        while (K < 2 * N - 1) {
            findAndJoin();
        }
    }

    public UPCluster getRoot() {
        return cluster[K - 1];
    }

    public double d(int i, int j) {
        return cluster[Math.max(i, j)].dmat[Math.min(i, j)];
    }

    void findAndJoin() { // Find closest two live clusters and join them
        int mini = -1, minj = -1;
        double mind = Double.POSITIVE_INFINITY;
        for (int i = 0; i < K; i++) {
            if (cluster[i].live()) {
                for (int j = 0; j < i; j++) {
                    if (cluster[j].live()) {
                        double d = d(i, j);
                        if (d < mind) {
                            mind = d;
                            mini = i;
                            minj = j;
                        }
                    }
                }
            }
        }
        join(mini, minj);
    }

    public void join(int i, int j) { // Join i and j to form node K
        String strSpecies = "Species_" + i + "_join_" + j;
        double[] dmat = new double[K];
        for (int m = 0; m < K; m++) {
            if (cluster[m].live() && m != i && m != j) {
                dmat[m] = (d(i, m) * cluster[i].card + d(j, m) * cluster[j].card)
                        / (cluster[i].card + cluster[j].card);
            }
        }
        cluster[K] = new UPCluster(strSpecies, cluster[i], cluster[j], dmat);
        cluster[i].kill();
        cluster[j].kill();
        K++;
    }
}

// Neighbour clusters or trees, built by the neighbour joining algorithm
class NJCluster extends Cluster {

    String label;		// Cluster identifier
    int card;			// The number of sequences in the cluster
    NJCluster left, right;	// Left and right children, or null
    double[] dmat;		// Distances to lower-numbered nodes, or null

    public NJCluster(String label, double[] dmat) {	// Leaves = single sequences
        this.label = label;
        card = 1;
        this.dmat = dmat;
    }

    public NJCluster(String label, NJCluster left, NJCluster right, double[] dmat) {
        this.label = label;
        this.left = left;
        this.right = right;
        card = left.card + right.card;
        this.dmat = dmat;
    }

    public boolean live() {
        return dmat != null;
    }

    public void kill() {
        dmat = null;
    }

    public void draw(Graphics g, int w, int h) {
        draw(g, w, h, 0, (double) w / card, 10);
    }

  // Draw tree and return x-coordinate of root
    public int draw(Graphics g, int w, int h, int leftcard, double xsc, int fromy) {
        if (left != null && right != null) {	// Internal node
            int y = fromy + 20;
            int leftx = left.draw(g, w, h, leftcard, xsc, y);
            int rightx = right.draw(g, w, h, leftcard + left.card, xsc, y);
            g.drawLine(leftx, y, rightx, y);
            int x = (leftx + rightx) / 2;
            g.drawLine(x, y, x, fromy);
            return x;
        } else {					// Leaf node
            int x = (int) ((leftcard + 0.5) * xsc);

            g.drawLine(x, fromy + 20, x, fromy);
            g.drawString(label, x - 3*label.length(), fromy + 20 + 15);

            return x;
        }
    }
}

// The neighbour-joining algorithm.  Make a rooted tree by arbitrarily
// adding a root node with edges to the last two leaves
class NJ {

    int N;			// The initial number of leaves
    int K;			// The number of clusters created so far
    NJCluster[] cluster;		// The nodes (clusters) of the resulting tree
    double[] r;			// The average distance to other leaves

    public NJ(String[] rgSpecies, double[][] ds) {
        N = ds.length;
        cluster = new NJCluster[2 * N - 1];
        
        assert(N == rgSpecies.length);
        
        for (int i = 0; i < N; i++) {
            cluster[i] = new NJCluster(rgSpecies[i], ds[i]);
        }
        r = new double[2 * N - 1];
        K = N;
        while (K < 2 * N - 2) {
            findAndJoin();
        }

        // Two leaves remain; cluster[K-1] is one of them, go find the other
        // Arbitrarily add a root node at this point
        int K2 = K - 2;
        while (!cluster[K2].live()) {
            K2--;
        }
        double dij = d(K2, K - 1) / 2;

        String strSpecies = "Species_" + K2 + "_join_" + (K-1);
        cluster[K] = new NJCluster(strSpecies, cluster[K2], cluster[K - 1], null);
        K++;
    }

    void computeR() {
        for (int i = 0; i < K; i++) {
            if (cluster[i].live()) {
                double sum = 0;
                for (int k = 0; k < K; k++) {
                    if (cluster[k].live() && k != i) {
                        sum += d(i, k);
                    }
                }
                int L = 2 * N - K;	// The current number of leaves
                r[i] = sum / (L - 2);	// Strange, but the book says so (p 171)
            }
        }
    }

    public NJCluster getRoot() {
        return cluster[K - 1];
    }

    public double d(int i, int j) {
        return cluster[Math.max(i, j)].dmat[Math.min(i, j)];
    }

    void findAndJoin() { // Find closest two live clusters and join them
        computeR();
        int mini = -1, minj = -1;
        double mind = Double.POSITIVE_INFINITY;
        for (int i = 0; i < K; i++) {
            if (cluster[i].live()) {
                for (int j = 0; j < i; j++) {
                    if (cluster[j].live()) {
                        double d = d(i, j) - (r[i] + r[j]);
                        if (d < mind) {
                            mind = d;
                            mini = i;
                            minj = j;
                        }
                    }
                }
            }
        }
        join(mini, minj);
    }

    public void join(int i, int j) { // Join i and j to form node K
        double[] dmat = new double[K];
        double dij = d(i, j);
        for (int m = 0; m < K; m++) {
            if (cluster[m].live() && m != i && m != j) {
                dmat[m] = (d(i, m) + d(j, m) - dij) / 2;
            }
        }
        double dik = (dij + r[i] - r[j]) / 2;
        double djk = dij - dik;
        
        String strSpecies = "Species_" + i + "_join_" + j;
        cluster[K] = new NJCluster(strSpecies, cluster[i], cluster[j], dmat);
        cluster[i].kill();
        cluster[j].kill();
        K++;
    }

    public boolean checkAdditivity() {
        for (int i = 0; i < N; i++) {
            for (int j = i + 1; j < N; j++) {
                for (int k = j + 1; k < N; k++) {
                    for (int m = k + 1; m < N; m++) {
                        double dijdkm = d(i, j) + d(k, m);
                        double dikdjm = d(i, k) + d(j, m);
                        double dimdjk = d(i, m) + d(j, k);
                        if (!(dijdkm == dikdjm && dijdkm >= dimdjk
                                || dijdkm == dimdjk && dijdkm >= dikdjm
                                || dikdjm == dimdjk && dikdjm >= dijdkm)) {
                            System.out.println("(i, j, k, m) = (" + i + "," + j + "," + k + "," + m + ")");
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
}

// Displaying and printing clusters or rooted trees
class TreeFrame extends ClosableFrame {

    String title;
    Button printButton = new Button("Print tree");
    TreeCanvas tc;

    public TreeFrame(String title, Cluster c, String dir) {
        super(title);
        this.title = title;
        tc = new TreeCanvas(c);
        add(tc, "Center");
        Panel p = new Panel();
        p.add(printButton);
        printButton.addActionListener(new buttonListener());
        add(p, "South");
        pack();
        show();
	BufferedImage img = new BufferedImage(tc.getWidth(), tc.getHeight(), BufferedImage.TYPE_INT_RGB);
    	tc.printAll(img.getGraphics()); // or: panel.printAll(...);
    	try {
        ImageIO.write(img, "jpg", new File(dir + title+".jpg"));
    	}
    	catch (IOException e) {
        // TODO Auto-generated catch block
        e.printStackTrace();
        }
	//setVisible(false);
	//dispose();
    }

    public void setCluster(Cluster c) {
        tc.setCluster(c);
    }

    public class buttonListener implements ActionListener {

        public void actionPerformed(ActionEvent e) {
            Toolkit t = getToolkit();
            PrintJob pj = t.getPrintJob(TreeFrame.this, "Printing " + title, null);
            if (pj != null) {
                Graphics pg = pj.getGraphics();
                tc.printAll(pg);
                pg.dispose();
                pj.end();
            }
        }
    }
}

class TreeCanvas extends Canvas {

    Cluster c;

    public TreeCanvas(Cluster c) {
        this.c = c;
    }

    public void setCluster(Cluster c) {
        this.c = c;
        repaint();
    }

    public void paint(Graphics g) {
        Dimension d = getSize();
        if (c != null) {
            c.draw(g, d.width, d.height);
        }
    }

    public Dimension getPreferredSize() {
        return new Dimension(600, 250);
    }

    public Dimension getMinimumSize() {
        return getPreferredSize();
    }
}

public class Match7 {

    public static void main(String[] args) {
        String FILENAME = args[0] + "SpeciesFull.txt";
	//System.out.println(args[0]);
      BufferedReader br = null;
      FileReader fr = null;
     
      int i = 0;
		try {

			fr = new FileReader(FILENAME);
			br = new BufferedReader(fr);
                        String sCurrentLine;
                        br = new BufferedReader(new FileReader(FILENAME));
                        while ((sCurrentLine = br.readLine()) != null) {
				i++;
			}

		} catch (IOException e) {

			e.printStackTrace();

		} 
                try {

				if (br != null)
					br.close();

				if (fr != null)
					fr.close();

			} catch (IOException ex) {

				ex.printStackTrace();

			}
                String [] speciesList = new String[ i ];
                double [][] diffMatrix = new double[i][i]; 
                i = 0;
                try {

			fr = new FileReader(FILENAME);
			br = new BufferedReader(fr);

			String sCurrentLine;

			br = new BufferedReader(new FileReader(FILENAME));

			while ((sCurrentLine = br.readLine()) != null) {
				speciesList[i] = sCurrentLine;
                                i++;
			}

		} catch (IOException e) {

			e.printStackTrace();

		}
                
        for ( int cnt = 0 ; cnt < speciesList.length ; cnt++ )
        {
            System.out.println(speciesList[cnt]);
        }
                try {
                        FILENAME = args[0] + "DistanceMatrix.txt";
			fr = new FileReader(FILENAME);
			br = new BufferedReader(fr);
                        br = new BufferedReader(new FileReader(FILENAME));
                        diffMatrix[0][0] = 0.0;
                        
                        for ( int j = 1 ; j < i ; j++ )
                        {
                            for ( int k = 0 ; k < j ; k++ )
                            {
                                diffMatrix[j][k] = Double.parseDouble(br.readLine().trim());
                            }
                        }

		} catch (IOException e) {

			e.printStackTrace();

		}
                for ( int j = 0 ; j < i ; j++ )
                {
                    for ( int k = 0 ; k < j ; k++ )
                    {
                        System.out.print(diffMatrix[j][k] + "  ");
                    }
                    System.out.println();
                }

        UPGMA upclu = new UPGMA(speciesList, diffMatrix);
        new TreeFrame("UPGMA tree", upclu.getRoot(), args[0]);

        NJ njclu = new NJ(speciesList, diffMatrix);
        new TreeFrame("Neighbour tree", njclu.getRoot(), args[0]);
		System.exit(1);
    }
}

class CloseListener extends WindowAdapter {

    public void windowClosing(WindowEvent e) {
        e.getWindow().dispose();
        System.exit(0);
    }
}

class ClosableFrame extends Frame {

    public ClosableFrame(String name) {
        super(name);
        addWindowListener(new CloseListener());
    }
}
