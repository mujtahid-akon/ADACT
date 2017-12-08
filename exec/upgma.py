# A Quick Implementation of UPGMA (Unweighted Pair Group Method with Arithmetic Mean)

# lowest_cell:
#   Locates the smallest cell in the table
import sys
from graphviz import Graph
ps = Graph(filename='UPGMATree', node_attr={'shape': 'plaintext'})
ps.format = 'jpg'
#ps.render()

def lowest_cell(table):
    # Set default to infinity
    min_cell = float("inf")
    x, y = -1, -1

    # Go through every cell, looking for the lowest
    for i in range(len(table)):
        for j in range(len(table[i])):
            if table[i][j] < min_cell:
                min_cell = table[i][j]
                x, y = i, j

    # Return the x, y co-ordinate of cell
    return x, y


# join_labels:
#   Combines two labels in a list of labels
def join_labels(labels, a, b):
    # Swap if the indices are not ordered
    if b < a:
        a, b = b, a

    # Join the labels in the first index
    labels[a] = "(" + labels[a] + "," + labels[b] + ")"

    # Remove the (now redundant) label in the second index
    del labels[b]


# join_table:
#   Joins the entries of a table on the cell (a, b) by averaging their data entries
def join_table(table, a, b):
    # Swap if the indices are not ordered
    if b < a:
        a, b = b, a

    # For the lower index, reconstruct the entire row (A, i), where i < A
    row = []
    for i in range(0, a):
        row.append((table[a][i] + table[b][i])/2)
    table[a] = row
    
    # Then, reconstruct the entire column (i, A), where i > A
    #   Note: Since the matrix is lower triangular, row b only contains values for indices < b
    for i in range(a+1, b):
        table[i][a] = (table[i][a]+table[b][i])/2
        
    #   We get the rest of the values from row i
    for i in range(b+1, len(table)):
        table[i][a] = (table[i][a]+table[i][b])/2
        # Remove the (now redundant) second index column entry
        del table[i][b]

    # Remove the (now redundant) second index row
    del table[b]


# UPGMA:
#   Runs the UPGMA algorithm on a labelled table
def UPGMA(table, labels):
    # Until all labels have been joined...
    while len(labels) > 1:
        # Locate lowest cell in the table
        x, y = lowest_cell(table)

        # Join the table on the cell co-ordinates
        join_table(table, x, y)

        # Update the labels accordingly
        join_labels(labels, x, y)

    # Return the final label
    return labels[0]



## A test using an example calculation from http://www.nmsr.org/upgma.htm

# alpha_labels:
#   Makes labels from a starting letter to an ending letter
def alpha_labels(start, end):
    labels = []
    for i in range(ord(start), ord(end)+1):
        labels.append(chr(i))
    return labels

# Test table data and corresponding labels
numberofspecies = int(sys.argv[3])

f = open(sys.argv[1],"r")
M_actual = []
M_labels = []
for line in f:
    M_actual.append(line)
for i in range(numberofspecies):
    ps.node(chr(i+65), M_actual[i], shape='egg', color='yellow')
    M_labels.append(chr(i+65))
f.close()

f = open(sys.argv[2],"r")
M = []

i = 0
while ( i < numberofspecies ):
    j = 0
    temp = []
    while j < i:
        temp.append(float(f.readline()))
        j = j + 1
    M.append(temp)
    i = i + 1



traversal = UPGMA(M, M_labels)
print traversal

length = len(traversal)

edge_list1 = []
edge_list2 = []

node = 2 * numberofspecies - 2
stack = []
for i in traversal:
    if i == '(':
        if len(stack) > 0:
            edge_list1.append(str(node))
            edge_list2.append(str(stack[len(stack) - 1]))
        ps.node(str(node), '',  shape='point', color='blue')
        stack.append(node)
        node = node - 1
    elif i == ')':
        stack.pop()
    elif i == ',':
        continue
    else:
        
        edge_list1.append(i)
        edge_list2.append(str(stack[len(stack) - 1]))
for i in range(len(edge_list1)):
    ps.edge(edge_list2[i], edge_list1[i], color='red')
		
ps.render()
